<?php

return [
    'db' => [
        'driver' => 'sqlite',
        'database' => dirname(__DIR__) . '/database/chatapp.db',
    ],
    'jwt' => [
        'secret' => 'this is the secret you need for the app',
        'algorithm' => 'HS256',
        'issuer' => 'chat',
        'issued_at' => time(),
        'expiration_time' => time() + 3600,
    ],
    'logger' => [
            'path' => __DIR__ . '/../logs/app.log',
    ],
];
