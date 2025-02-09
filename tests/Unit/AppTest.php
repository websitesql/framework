<?php

use AlanTiller\Framework\Core\App;
use AlanTiller\Framework\Core\Config;
use Psr\Log\NullLogger;

it('can create app instance', function () {
    $app = new App(__DIR__ . '/../../', new NullLogger());
    expect($app)->toBeInstanceOf(App::class);
});

it('can get config', function () {
    $app = new App(__DIR__ . '/../../', new NullLogger());
    $app->init();
    expect($app->getConfig())->toBeInstanceOf(Config::class);
});

it('can get base path', function () {
    $app = new App(__DIR__ . '/../../', new NullLogger());
    expect($app->getBasePath())->toBe(__DIR__ . '/../../');
});