<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Cravler\FayeAppBundle\EntryPoint\Example;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
    ;

    $services->set(Example::class);
};
