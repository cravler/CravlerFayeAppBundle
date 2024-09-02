<?php

namespace Cravler\FayeAppBundle\Controller;

use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\Ext\AppExtInterface;
use Cravler\FayeAppBundle\Service\EntryPointsChain;
use Cravler\FayeAppBundle\Service\ExtensionsChain;
use Cravler\FayeAppBundle\Service\SecurityManager;
use Cravler\FayeAppBundle\Twig\FayeAppExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class AppController extends AbstractController
{
    private ?TokenStorageInterface $tokenStorage;

    public function __construct(
        ?TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    protected function getInitConfig(Request $request): array
    {
        /** @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $security = [];
        if ($this->tokenStorage && $this->tokenStorage->getToken()) {
            if ($this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
                /** @var UserInterface $user */
                $user = $this->tokenStorage->getToken()->getUser();
                $userIdentifier = \method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername();

                $security = [
                    'userIdentifier' => $userIdentifier,
                    'token' => $sm->createToken($userIdentifier),
                ];
            }
        }

        $token = isset($security['token']) ? $security['token'] : 'anonymous';

        $security['url'] = $this->generateUrl('faye_app_security', [], 0);
        $security['url.hash'] = \md5($token.';'.$security['url'].';'.$config['security_url_salt']);

        return [
            'url' => FayeAppExtension::generateUri($request, $config),
            'options' => $config['app']['options'],
            'security' => $security,
            'entry_point_prefix' => $config['entry_point_prefix'],
        ];
    }

    public function configAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->getInitConfig($request));
    }

    public function initAction(Request $request, ?string $connection = null): Response
    {
        $content = 'FayeApp.init('.\json_encode(
            $this->getInitConfig($request),
            \JSON_PRETTY_PRINT|\JSON_FORCE_OBJECT
        ).');'.\PHP_EOL;

        /** @var ExtensionsChain $extChain */
        $extChain = $this->container->get('cravler_faye_app.service.extensions_chain');

        foreach ($extChain->getExtensions() as $extension) {
            if ($extension instanceof AppExtInterface) {
                $content .= $extension->getAppExt($connection).\PHP_EOL;
            }
        }

        $content = \implode(\PHP_EOL, [
            '(function(FayeApp) {',
            \implode(\PHP_EOL, \array_map(
                fn (string $line) => \str_repeat(' ', 4).$line,
                \explode(\PHP_EOL, \trim($content)))
            ),
            '})(FayeApp'.($connection ? '.connection(\''.$connection.'\')' : '').');',
        ]).\PHP_EOL;

        return new Response($content, Response::HTTP_OK, ['Content-Type' => $request->getMimeType('js')]);
    }

    public function securityAction(Request $request): JsonResponse
    {
        /** @var EntryPointsChain $entryPointsChain */
        $entryPointsChain = $this->container->get('cravler_faye_app.service.entry_points_chain');

        /** @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        $response = [
            'success' => false,
            'cache' => false,
        ];

        $type = null;
        $channel = null;
        $entryPoint = null;

        if ('json' === $request->getContentType()) {
            $content = $request->getContent();
            if (!empty($content) && \is_array($arr = \json_decode($content, true))) {
                $request->request->replace($arr);
            }
        }

        $data = $request->request->all();
        if (isset($data['channel'])) {
            if ('/meta/subscribe' === $data['channel']) {
                $type = EntryPointInterface::TYPE_SUBSCRIBE;
                $channel = $data['subscription'];
            } else {
                $type = EntryPointInterface::TYPE_PUBLISH;
                $channel = $data['channel'];
            }
        }

        if ($channel) {
            $key = \explode('/', $channel, 3);
            if (3 === \count($key)) {
                $channel = '/'.$key[2];
                $entryPoint = $entryPointsChain->getEntryPoint(\explode('@', \str_replace('~', '.', $key[1]), 2)[1]);
            }
        }

        $message = [
            'ext' => isset($data['ext']) ? $data['ext'] : [],
            'data' => isset($data['data']) ? $data['data'] : [],
            'clientId' => isset($data['clientId']) ? $data['clientId'] : null,
        ];

        if (isset($message['ext']['security']) && $sm->isSystem($message['ext']['security'])) {
            $response['success'] = true;
        } elseif ($entryPoint && $entryPoint->isGranted($type, $channel, $message)) {
            $response['success'] = true;
            $response['cache'] = $entryPoint->useCache($type, $channel, $message);
        }

        if (false === $response['success'] && !isset($response['msg'])) {
            $response['msg'] = Response::HTTP_FORBIDDEN.'::'.Response::$statusTexts[Response::HTTP_FORBIDDEN];
        }

        return new JsonResponse($response);
    }

    public function statusAction(Request $request): Response
    {
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $appCfg = $config['app'];
        $healthCheckCfg = $config['health_check'];

        $scheme = $appCfg['scheme'] ?: $request->getScheme();
        $url = $scheme.'://'.$appCfg['host'];
        $port = 'https' == $scheme ? 443 : 80;
        if ($appCfg['port']) {
            $url = $url.':'.$appCfg['port'];
            $port = $appCfg['port'];
        }
        $url = $url.($healthCheckCfg['path'] ?: $appCfg['mount']);

        $status = Response::HTTP_SERVICE_UNAVAILABLE;
        $content = Response::$statusTexts[Response::HTTP_SERVICE_UNAVAILABLE];
        try {
            $fp = \fsockopen($appCfg['host'], $port, $errCode, $errStr, 1);
            if ($fp) {
                \stream_context_set_default([
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ),
                ]);

                $headers = \get_headers($url);
                $code = \intval(\substr($headers[0], 9, 3));
                $responseCode = \intval($healthCheckCfg['response_code'] ?: Response::HTTP_BAD_REQUEST);

                if ($responseCode === $code) {
                    $status = Response::HTTP_OK;
                    $content = Response::$statusTexts[Response::HTTP_OK];
                }
            }
            \fclose($fp);
        } catch (\Exception $e) {}

        return new Response($content, $status);
    }

    public function exampleAction(Request $request, ?string $type = null): array
    {
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        if (!$config['example']) {
            throw $this->createNotFoundException();
        }

        /** @var SecurityManager $sm */
        $sm = $this->container->get('cravler_faye_app.service.security_manager');

        return $this->render(
            '@CravlerFayeApp/App/example.html.twig',
            [
                'system' => 'system' === $type,
                'security' => [
                    'system' => $sm->createSystemToken(),
                ],
            ]
        );
    }
}
