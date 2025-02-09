<?php

return [
    'type' => env('DB_DRIVER', 'mysql'),
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_NAME', 'database'),
    'username' => env('DB_USER', 'user'),
    'password' => env('DB_PASS', 'password'),
    'prefix' => env('DB_PREFIX', ''),
];