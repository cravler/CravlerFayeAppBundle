<?php

namespace Cravler\FayeAppBundle\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class FayeAppExtension extends \Twig_Extension
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
            'faye_app_javascript' => new \Twig_Function_Method($this, 'getJavascript', array('is_safe' => array('html'))),
            'faye_app_uri'        => new \Twig_Function_Method($this, 'getUri'),
        );
    }

    /**
     * @return string
     */
    public function getJavascript()
    {
        return $this->container->get('templating')->render('CravlerFayeAppBundle:App:javascript.html.twig');
    }

    /**
     * @return string
     */
    public function getUri()
    {
        /* @var Request $request */
        $request = $this->container->get('request');
        $config = $this->container->getParameter(CravlerFayeAppExtension::CONFIG_KEY);

        return self::generateUri($request, $config);
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
            $url = $config['app']['scheme'] . '://' . $config['app']['host'];
        }

        return $url . ':' . $config['app']['port'] . $config['app']['mount'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cravler_faye_app_twig_extension';
    }
}
