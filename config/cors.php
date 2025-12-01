<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5175',
        'http://127.0.0.1:5175',
        "http://localhost:5173",
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => true,
];
