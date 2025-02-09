<?php

use AlanTiller\Framework\Core\App;

return function (App $app) {
    $router = $app->getRouter();

    $router->addRoute('GET', '/', function () {
        return 'Hello, world!';
    });
};