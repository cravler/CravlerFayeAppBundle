<?php

namespace Cravler\FayeAppBundle\DependencyInjection;

use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\Service\SecurityManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CravlerFayeAppExtension extends Extension
{
    public const CONFIG_KEY = 'cravler_faye_app.config';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');

        if ($config['example']) {
            $loader->load('example/services.php');
        }

        $container->setParameter(self::CONFIG_KEY, $config);
        foreach ($config as $key => $value) {
            $container->setParameter(self::CONFIG_KEY.'.'.$key, $value);
        }

        if (false !== $config['user_provider']) {
            $definition = $container->getDefinition(SecurityManager::class);
            $definition->setArgument('$provider', new Reference($config['user_provider']));
        }

        $container->registerForAutoconfiguration(EntryPointInterface::class)
            ->addTag('cravler_faye_app.entry_point')
        ;
    }
}
