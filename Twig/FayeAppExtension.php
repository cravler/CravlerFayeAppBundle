<?php

namespace Cravler\FayeAppBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment as TwigEnvironment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class FayeAppExtension extends AbstractExtension
{
    /**
     * @param array{
     *      'app': array{
     *          'scheme'?: string,
     *          'host': string,
     *          'port'?: int,
     *          'mount': string,
     *      },
     *      'use_request_uri'?: bool,
     *  } $config
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TwigEnvironment $twig,
        private readonly array $config,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('faye_app_javascript', [$this, 'getJavascript'], ['is_safe' => ['html']]),
            new TwigFunction('faye_app_uri', [$this, 'getUri']),
        ];
    }

    public function getJavascript(): string
    {
        return $this->twig->render('@CravlerFayeApp/App/javascript.html.twig');
    }

    public function getUri(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return '';
        }

        return self::generateUri($request, $this->config);
    }

    /**
     * @param array{
     *     'app': array{
     *         'scheme'?: string,
     *         'host': string,
     *         'port'?: int,
     *         'mount': string,
     *     },
     *     'use_request_uri'?: bool,
     * } $config
     */
    public static function generateUri(Request $request, array $config): string
    {
        if ($config['use_request_uri'] ?? false) {
            $url = $request->getScheme().'://'.$request->getHost();
        } else {
            $scheme = $config['app']['scheme'] ?? $request->getScheme();

            $url = $scheme.'://'.$config['app']['host'];

            if ($config['app']['port'] ?? null) {
                $port = $config['app']['port'];

                if (80 === $port && 'http' === $scheme) {
                    $port = null;
                } elseif (443 === $port && 'https' === $scheme) {
                    $port = null;
                }

                if ($port) {
                    $url = $url.':'.$port;
                }
            }
        }

        return $url.$config['app']['mount'];
    }

    public function getName(): string
    {
        return 'cravler_faye_app_twig_extension';
    }
}
