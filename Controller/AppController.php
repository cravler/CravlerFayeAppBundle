<?php

namespace Cravler\FayeAppBundle\Controller;

use Cravler\FayeAppBundle\Ext\AppExtInterface;
use Cravler\FayeAppBundle\Twig\FayeAppExtension;
use Cravler\FayeAppBundle\Service\SecurityManager;
use Cravler\FayeAppBundle\Service\ExtensionsChain;
use Cravler\FayeAppBundle\Service\EntryPointsChain;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class AppController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    protected function getInitConfig(Request $request)
    {
        /* @var TokenStorageInterface $ts */
        $ts = $this->container->get('security.token_storage');

        /* @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $security = array();
        if ($ts->getToken()) {
            if ($ts->getToken()->getUser() instanceof UserInterface) {
                /* @var UserInterface $user */
                $user = $ts->getToken()->getUser();
                $security = array(
                    'username' => $user->getUsername(),
                    'token'    => $sm->createToken($user->getUsername()),
                );
            }
        }

        return array(
            'url'                => FayeAppExtension::generateUri($request, $config),
            'options'            => $config['app']['options'],
            'security'           => $security,
            'entry_point_prefix' => $config['entry_point_prefix'],
        );
    }

    /**
     * @Route("/config.json", name="faye_app_config")
     * @param Request $request
     * @return JsonResponse
     */
    public function configAction(Request $request)
    {
        return new JsonResponse($this->getInitConfig($request));
    }

    /**
     * @Route("/init.js", name="faye_app_init")
     * @param Request $request
     * @return Response
     */
    public function initAction(Request $request)
    {
        $content = 'FayeApp.connect(' . json_encode(
            $this->getInitConfig($request),
            JSON_PRETTY_PRINT|JSON_FORCE_OBJECT
        ) . ');' . PHP_EOL;

        /* @var ExtensionsChain $extChain */
        $extChain = $this->container->get('cravler_faye_app.service.extensions_chain');

        foreach ($extChain->getExtensions() as $extension) {
            if ($extension instanceof AppExtInterface) {
                $content .= $extension->getAppExt() . PHP_EOL;
            }
        }

        return new Response($content, 200, array('Content-Type' => $request->getMimeType('js')));
    }

    /**
     * @Route("/security", name="faye_app_security")
     * @param Request $request
     * @return JsonResponse
     */
    public function securityAction(Request $request)
    {
        /* @var EntryPointsChain $entryPointsChain */
        $entryPointsChain = $this->container->get('cravler_faye_app.service.entry_points_chain');

        /* @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $response = array(
            'success' => false,
            'cache'   => false,
        );

        $type = null;
        $channel = null;
        $entryPoint = null;

        $data = $request->request->all();
        if (isset($data['channel'])) {
            if ($data['channel'] === '/meta/subscribe') {
                $type = EntryPointInterface::TYPE_SUBSCRIBE;
                $channel = $data['subscription'];
            } else {
                $type = EntryPointInterface::TYPE_PUBLISH;
                $channel = $data['channel'];
            }
        }


        if ($channel) {
            $key = explode('/', $channel, 3);
            if (count($key) == 3) {
                $channel = '/' . $key[2];
                $entryPoint = $entryPointsChain->getEntryPoint(explode('@', str_replace('~', '.', $key[1]), 2)[1]);
            }
        }

        $message = array(
            'ext'      => isset($data['ext']) ? $data['ext'] : array(),
            'data'     => isset($data['data']) ? $data['data'] : array(),
            'clientId' => isset($data['clientId']) ? $data['clientId'] : null,
        );

        if (isset($message['ext']['security']) && $sm->isSystem($message['ext']['security'])) {
            $response['success'] = true;
        } else if ($entryPoint && $entryPoint->isGranted($type, $channel, $message)) {
            $response['success'] = true;
            $response['cache'] = $entryPoint->useCache($type, $channel, $message);
        }

        if ($response['success'] === false && !isset($response['msg'])) {
            $response['msg'] = '403::Forbidden';
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/status", name="faye_app_status")
     * @param Request $request
     * @return Response
     */
    public function statusAction(Request $request)
    {
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $appCfg = $config['app'];
        $healthCheckCfg = $config['health_check'];

        $scheme = $appCfg['scheme'] ?: $request->getScheme();
        $url = $scheme . '://' . $appCfg['host'];
        $port = 'https' == $scheme ? 443 : 80;
        if ($appCfg['port']) {
            $url = $url . ':' . $appCfg['port'];
            $port = $appCfg['port'];
        }
        $url = $url . ($healthCheckCfg['path'] ?: $appCfg['mount']);

        $status = 503;
        $content = 'Service Unavailable';
        try {
            $fp = fsockopen($appCfg['host'], $port, $errCode, $errStr, 1);
            if ($fp) {
                stream_context_set_default(array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ),
                ));

                $headers = get_headers($url);
                $code = intval(substr($headers[0], 9, 3));
                $responseCode = $healthCheckCfg['response_code'] ?: 400;

                if ($responseCode == $code) {
                    $status = 200;
                    $content = 'OK';
                }
            }
            fclose($fp);
        } catch (\Exception $e) {}

        return new Response($content, $status);
    }

    /**
     * @Route("/example/{type}", defaults={"type"=null}, name="faye_app_example")
     * @Template()
     */
    public function exampleAction(Request $request, $type = null)
    {
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        if (!$config['example']) {
            throw $this->createNotFoundException();
        }

        /* @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        return array(
            'system'   => $type == 'system',
            'security' => array(
                'system' => $sm->createSystemToken(),
            ),
        );
    }
}
