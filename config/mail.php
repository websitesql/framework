<?php

return [
    'driver' => env('MAIL_DRIVER', 'smtp'), // smtp, mail, log
    'debug' => env('MAIL_DEBUG', false),
    'host' => env('MAIL_HOST', 'smtp.example.com'),
    'port' => env('MAIL_PORT', 587),
    'auth' => env('MAIL_AUTH', true),
    'username' => env('MAIL_USERNAME', 'your_username'),
    'password' => env('MAIL_PASSWORD', 'your_password'),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'), // tls or ssl
    'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'from_name' => env('MAIL_FROM_NAME', 'Example'),
];