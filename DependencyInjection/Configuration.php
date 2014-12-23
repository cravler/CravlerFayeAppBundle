<?php

namespace Cravler\FayeAppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cravler_faye_app');

        $rootNode
            ->children()
                ->booleanNode('example')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('user_provider')
                    ->defaultValue(false) //security.user.provider.concrete.[provider_name]
                ->end()
                ->scalarNode('route_url_prefix')
                    ->defaultValue('faye-app')
                ->end()
                ->booleanNode('use_request_uri')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('app')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('scheme')
                            ->defaultValue('http')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('127.0.0.1')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue(8000)
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('mount')
                            ->defaultValue('/pub-sub')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('options')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('secret')
                    ->defaultValue('ThisTokenIsNotSoSecretChangeIt')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
