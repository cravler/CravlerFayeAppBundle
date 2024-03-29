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
        $treeBuilder = new TreeBuilder('cravler_faye_app');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('example')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('user_provider')
                    ->defaultValue(false) //security.user.provider.concrete.[provider_name]
                ->end()
                ->scalarNode('route_url_prefix')
                    ->defaultValue('/faye-app')
                ->end()
                ->booleanNode('use_request_uri')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('entry_point_prefix')
                    ->defaultValue('')
                ->end()
                ->scalarNode('security_url_salt')
                    ->defaultValue('')
                ->end()
                ->arrayNode('app')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('scheme')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('host')
                            ->defaultValue('127.0.0.1')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('port')
                            ->defaultValue(null)
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
                ->arrayNode('client_adapter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('connect_timeout')
                            ->defaultValue(2)
                        ->end()
                        ->booleanNode('insecure')
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('health_check')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')
                            ->defaultValue('/pub-sub/client.js')
                        ->end()
                        ->scalarNode('response_code')
                            ->defaultValue(200)
                            ->cannotBeEmpty()
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
