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
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
#[AsController]
class AppController extends AbstractController
{
    public function __construct(
        private readonly EntryPointsChain $entryPointsChain,
        private readonly ExtensionsChain $extensionsChain,
        private readonly SecurityManager $securityManager,
        private readonly ?TokenStorageInterface $tokenStorage = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInitConfig(Request $request): array
    {
        /** @var array{
         *      'app': array{
         *          'scheme'?: string,
         *          'host': string,
         *          'port'?: int,
         *          'mount': string,
         *          'options': array<string, mixed>,
         *      },
         *      'entry_point_prefix': string,
         *      'security_url_salt': string,
         * } $config
         */
        $config = $this->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $security = [];
        if ($this->tokenStorage && $this->tokenStorage->getToken()) {
            if ($this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
                /** @var UserInterface $user */
                $user = $this->tokenStorage->getToken()->getUser();
                $userIdentifier = $user->getUserIdentifier();

                $security = [
                    'userIdentifier' => $userIdentifier,
                    'token' => $this->securityManager->createToken($userIdentifier),
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

    #[Route('/config.json', methods: [Request::METHOD_GET], name: 'faye_app_config')]
    public function configAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->getInitConfig($request));
    }

    #[Route('/init.js', methods: [Request::METHOD_GET], name: 'faye_app_init')]
    #[Route('/{connection}/init.js', methods: [Request::METHOD_GET], name: 'faye_app_init_by_connection')]
    public function initAction(Request $request, ?string $connection = null): Response
    {
        $content = 'FayeApp.init('.\json_encode(
            $this->getInitConfig($request),
            \JSON_PRETTY_PRINT | \JSON_FORCE_OBJECT
        ).');'.\PHP_EOL;

        foreach ($this->extensionsChain->getExtensions() as $extension) {
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

    #[Route('/security', methods: [Request::METHOD_POST], name: 'faye_app_security')]
    public function securityAction(Request $request): JsonResponse
    {
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
                /** @var string $channel */
                $channel = $data['subscription'];
            } else {
                $type = EntryPointInterface::TYPE_PUBLISH;
                /** @var string $channel */
                $channel = $data['channel'];
            }
        }

        if ($channel) {
            $key = \explode('/', $channel, 3);
            if (3 === \count($key)) {
                $channel = '/'.$key[2];
                $entryPoint = $this->entryPointsChain->getEntryPoint(\explode('@', \str_replace('~', '.', $key[1]), 2)[1]);
            }
        }

        $message = [
            'ext' => \is_array($data['ext'] ?? null) ? $data['ext'] : [],
            'data' => \is_array($data['data'] ?? null) ? $data['data'] : [],
            'clientId' => \is_string($data['clientId'] ?? null) ? $data['clientId'] : null,
        ];

        if (\is_array($message['ext']['security'] ?? null) && $this->securityManager->isSystem($message['ext']['security'])) {
            $response['success'] = true;
        } elseif (\is_string($type) && \is_string($channel) && $entryPoint?->isGranted($type, $channel, $message)) {
            $response['success'] = true;
            $response['cache'] = $entryPoint->useCache($type, $channel, $message);
        }

        if (false === $response['success']) {
            $response['msg'] = Response::HTTP_FORBIDDEN.'::'.Response::$statusTexts[Response::HTTP_FORBIDDEN];
        }

        return new JsonResponse($response);
    }

    #[Route('/status', methods: [Request::METHOD_GET], name: 'faye_app_status')]
    public function statusAction(Request $request): Response
    {
        /** @var array{
         *      'app': array{
         *          'scheme'?: string,
         *          'host': string,
         *          'port'?: int,
         *          'mount': string,
         *      },
         *      'health_check': array{
         *          'path': string,
         *          'response_code': int,
         *      },
         * } $config
         */
        $config = $this->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        $appCfg = $config['app'];
        $healthCheckCfg = $config['health_check'];

        $scheme = $appCfg['scheme'] ?? $request->getScheme();
        $url = $scheme.'://'.$appCfg['host'];
        $port = 'https' === $scheme ? 443 : 80;
        if ($appCfg['port'] ?? null) {
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
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);

                $headers = \get_headers($url);
                $code = $headers ? \intval(\substr($headers[0], 9, 3)) : 0;
                $responseCode = \intval($healthCheckCfg['response_code'] ?: Response::HTTP_BAD_REQUEST);

                if ($responseCode === $code) {
                    $status = Response::HTTP_OK;
                    $content = Response::$statusTexts[Response::HTTP_OK];
                }

                \fclose($fp);
            }
        } catch (\Exception $e) {
        }

        return new Response($content, $status);
    }

    #[Route('/example/{type}', methods: [Request::METHOD_GET], name: 'faye_app_example', defaults: ['type' => null])]
    public function exampleAction(Request $request, ?string $type = null): Response
    {
        /** @var array{
         *      'example': bool,
         * } $config
         */
        $config = $this->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        if (!$config['example']) {
            throw $this->createNotFoundException();
        }

        return $this->render(
            '@CravlerFayeApp/App/example.html.twig',
            [
                'system' => 'system' === $type,
                'security' => [
                    'system' => $this->securityManager->createSystemToken(),
                ],
            ]
        );
    }
}
