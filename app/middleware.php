<?php

use DI\Container;
use Presentation\Middleware\AuthMiddleware;
use Presentation\Middleware\CorsMiddleware;
use Presentation\Middleware\CspMiddleware;

return function (Container $container) {
    $settings = $container->get('settings');

    // Register AuthMiddleware
    $container->set(AuthMiddleware::class, function () use ($settings) {
        return new AuthMiddleware($settings['jwt']['secret'], $settings['jwt']['algorithm']);
    });

    // Register CORS Middleware
    $container->set(CorsMiddleware::class, function () {
        return new CorsMiddleware();
    });

    // Register CSP Middleware
    $container->set(CspMiddleware::class, function () {
        return new CspMiddleware();
    });
};
