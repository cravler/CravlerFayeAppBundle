<?php

namespace Cravler\FayeAppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CravlerFayeAppExtension extends Extension
{
    const CONFIG_KEY = 'cravler_faye_app.config';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $definition = new Definition('Cravler\FayeAppBundle\Twig\FayeAppExtension');
        $definition->addTag('twig.extension');
        $definition->addArgument(new Reference('service_container'));
        $container->setDefinition('cravler_faye_app_twig_extension', $definition);

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controller.xml');
        $loader->load('services.xml');

        if (interface_exists('Sli\ExpanderBundle\Ext\ContributorInterface')) {
            $loader->load('routing.xml');
        }

        if ($config['example']) {
            $loader->load('example/services.xml');
        }

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY . '.' . $key, $value);
        }

        if (false !== $config['user_provider']) {
            $definition = $container->getDefinition('cravler_faye_app.service.security_manager');
            $definition->replaceArgument(1, new Reference($config['user_provider']));
        }
    }
}
