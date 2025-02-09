<?php

return [
    'allowedOrigins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')), // e.g., ['http://example.com', 'https://example.org'] or ['*']
    'allowedMethods' => explode(',', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,OPTIONS')),
    'allowedHeaders' => explode(',', env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,Accept')),
    'exposedHeaders' => explode(',', env('CORS_EXPOSED_HEADERS', '')),
    'maxAge' => env('CORS_MAX_AGE', 86400),
    'allowCredentials' => env('CORS_ALLOW_CREDENTIALS', false),
];