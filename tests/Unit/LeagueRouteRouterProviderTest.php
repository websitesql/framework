<?php

use AlanTiller\Framework\Providers\LeagueRouteRouterProvider;

it('can add route', function () {
    $router = new LeagueRouteRouterProvider();
    $router->addRoute('GET', '/', function () {
        return 'Hello, world!';
    });

    expect(true)->toBeTrue(); // Just check that it doesn't throw an error
});