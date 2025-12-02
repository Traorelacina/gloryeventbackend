<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'track-view',
        'track-pixel',
        'health',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5175',
        'http://127.0.0.1:5175',
        'http://localhost:5173',
        'https://gloryevent.netlify.app',
        'https://*.netlify.app', // AJOUTER CETTE LIGNE
    ],

    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.netlify\.app$/',
        '/^https?:\/\/localhost:\d+$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
