<?php

namespace Cravler\FayeAppBundle\Controller;

use Cravler\FayeAppBundle\Twig\FayeAppExtension;
use Cravler\FayeAppBundle\Service\SecurityManager;
use Cravler\FayeAppBundle\Service\EntryPointsChain;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class AppController extends Controller
{
    /**
     * @Route("/init.js", name="faye_app_init")
     */
    public function initAction(Request $request)
    {
        /* @var SecurityContextInterface $sc */
        $sc = $this->container->get('security.context');

        /* @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $security = array();
        if ($sc->getToken()) {
            if ($sc->getToken()->getUser() instanceof UserInterface) {
                $user = $sc->getToken()->getUser();
                $security = array(
                    'username' => $user->getUsername(),
                    'token'    => $sm->createToken($user->getUsername()),
                );
            }
        }

        $content = 'FayeApp.connect(' . json_encode(array(
                'url'      => FayeAppExtension::generateUri($request, $config),
                'security' => $security,
                'options'  => $config['app']['options'],
            ), JSON_PRETTY_PRINT|JSON_FORCE_OBJECT) . ');';

        return new Response($content, 200, array('Content-Type' => $request->getMimeType('js')));
    }

    /**
     * @Route("/security", name="faye_app_security")
     */
    public function securityAction(Request $request)
    {
        /* @var EntryPointsChain $entryPointsChain */
        $entryPointsChain = $this->container->get('cravler_faye_app.service.entry_points_chain');

        /* @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $response = array(
            'success' => false
        );

        $type = null;
        $channel = null;
        $entryPoint = null;

        $data = $request->request->all();
        if ($data['channel'] === '/meta/subscribe') {
            $type = EntryPointInterface::TYPE_SUBSCRIBE;
            $channel = $data['subscription'];
        } else {
            $type = EntryPointInterface::TYPE_PUBLISH;
            $channel = $data['channel'];
        }

        if ($channel) {
            $key = explode('/', $channel, 3);
            if (count($key) == 3) {
                $channel = '/' . $key[2];
                $entryPoint = $entryPointsChain->getEntryPoint(str_replace('~', '.', $key[1]));
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
        }

        if ($response['success'] === false && !isset($response['msg'])) {
            $response['msg'] = '403::Forbidden';
        }

        return new JsonResponse($response);
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
