<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->import(
            \dirname(__DIR__, 2).'/Controller/',
            'attribute',
        )
        ->prefix(
            '%cravler_faye_app.config.route_url_prefix%',
            false,
        )
    ;
};
