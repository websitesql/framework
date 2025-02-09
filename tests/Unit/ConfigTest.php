<?php

use AlanTiller\Framework\Core\Config;

it('can load config files', function () {
    $config = new Config(__DIR__ . '/../../config');
    expect($config->all())->toBeArray();
});

it('can get config value', function () {
    $config = new Config(__DIR__ . '/../../config');
    expect($config->get('app.timezone'))->toBe('UTC');
});

it('can get default value', function () {
    $config = new Config(__DIR__ . '/../../config');
    expect($config->get('nonexistent.key', 'default'))->toBe('default');
});