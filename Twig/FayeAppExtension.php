<?php

namespace Cravler\FayeAppBundle\Twig;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class FayeAppExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('faye_app_javascript', array($this, 'getJavascript'), array('is_safe' => array('html'))),
            new TwigFunction('faye_app_uri', array($this, 'getUri')),
        );
    }

    /**
     * @return string
     */
    public function getJavascript()
    {
        return $this->container->get('twig')->render('@CravlerFayeApp/App/javascript.html.twig');
    }

    /**
     * @return string
     */
    public function getUri()
    {
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        return self::generateUri($this->getRequest(), $config);
    }

    /**
     * @param Request $request
     * @param array $config
     * @return string
     */
    static function generateUri(Request $request, array $config)
    {
        if ($config['use_request_uri']) {
            $url = $request->getScheme() . '://' . $request->getHost();
        } else {
            $scheme = $config['app']['scheme'] ?: $request->getScheme();

            $url = $scheme . '://' . $config['app']['host'];

            if ($config['app']['port']) {
                $port = $config['app']['port'];

                if (80 == $port && 'http' == $scheme) {
                    $port = null;
                } else if (443 == $port && 'https' == $scheme) {
                    $port = null;
                }

                if ($port) {
                    $url = $url . ':' . $port;
                }
            }
        }

        return $url . $config['app']['mount'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cravler_faye_app_twig_extension';
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        /* @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');

        return $requestStack->getCurrentRequest();
    }
}
