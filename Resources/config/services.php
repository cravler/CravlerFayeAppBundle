<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cravler\FayeAppBundle\Client\Adapter\AdapterInterface;
use Cravler\FayeAppBundle\Client\Adapter\HttpClientAdapter;
use Cravler\FayeAppBundle\Client\Client;
use Cravler\FayeAppBundle\Client\ClientInterface;
use Cravler\FayeAppBundle\Controller\AppController;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;
use Cravler\FayeAppBundle\Service\EntryPointManager;
use Cravler\FayeAppBundle\Service\EntryPointsChain;
use Cravler\FayeAppBundle\Service\ExtensionsChain;
use Cravler\FayeAppBundle\Service\PackageManager;
use Cravler\FayeAppBundle\Service\SecurityManager;
use Cravler\FayeAppBundle\Twig\FayeAppExtension;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(HttpClientAdapter::class);
    $services
        ->alias(AdapterInterface::class, HttpClientAdapter::class)
    ;

    $services->set(Client::class)
        ->arg('$config', param(CravlerFayeAppExtension::CONFIG_KEY.'.app'))
    ;
    $services
        ->alias(ClientInterface::class, Client::class)
    ;

    $services->set(AppController::class);

    $services->set(EntryPointManager::class)
        ->arg('$entryPointPrefix', param(CravlerFayeAppExtension::CONFIG_KEY.'.entry_point_prefix'))
        ->arg('$securityUrlSalt', param(CravlerFayeAppExtension::CONFIG_KEY.'.security_url_salt'))
    ;
    $services
        ->alias('cravler_faye_app.service.entry_point_manager', EntryPointManager::class)
        ->public()
    ;

    $services->set(EntryPointsChain::class);
    $services
        ->alias('cravler_faye_app.service.entry_points_chain', EntryPointsChain::class)
        ->public()
    ;

    $services->set(ExtensionsChain::class);

    $services->set(PackageManager::class)
        ->tag('kernel.event_listener', [
            'event' => 'console.terminate',
            'method' => 'onTerminate',
        ])
        ->tag('kernel.event_listener', [
            'event' => 'kernel.terminate',
            'method' => 'onTerminate',
        ])
    ;

    $services->set(SecurityManager::class)
        ->arg('$secret', param(CravlerFayeAppExtension::CONFIG_KEY.'.secret'))
        ->arg('$provider', null)
    ;

    $services->set(FayeAppExtension::class)
        ->arg('$config', param(CravlerFayeAppExtension::CONFIG_KEY))
        ->tag('twig.extension')
    ;
};
