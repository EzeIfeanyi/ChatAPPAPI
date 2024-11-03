<?php

use DI\Container;
use Application\Services\UserService;
use Application\Services\GroupService;
use Application\Services\GroupServiceInterface;
use Application\Services\MessageService;
use Application\Services\MessageServiceInterface;
use Application\Services\UserServiceInterface;
use Application\Repositories\GroupMemberRepositoryInterface;
use Application\Repositories\UserRepositoryInterface;
use Infrastructure\Database\DatabaseConnectionInterface;
use Infrastructure\Database\SQLiteConnection;
use Application\Repositories\GroupRepositoryInterface;
use Application\Repositories\MessageRepositoryInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return function (Container $container) {
    $settings = $container->get('settings');

    // Register the DatabaseConnectionInterface in the container
    $container->set(DatabaseConnectionInterface::class, function () use ($settings) {
        $dbFile = $settings['db']['database'];
        return new SQLiteConnection($dbFile);
    });

    // Register the logger
    $container->set(Logger::class, function () use ($settings) {
        $logger = new Logger('app-logger');
        $logFile = $settings['logger']['path'] ?? __DIR__ . '/../logs/app.log';
        $logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
        return $logger;
    });

    // Register services
    $container->set(UserServiceInterface::class, function () use ($container) {
        $settings = $container->get('settings')['jwt'];
        return new UserService(
            $container->get(UserRepositoryInterface::class), 
            $settings, 
            $container->get(Logger::class)
        );
    });

    $container->set(GroupServiceInterface::class, function () use ($container) {
        return new GroupService(
            $container->get(GroupRepositoryInterface::class),
            $container->get(GroupMemberRepositoryInterface::class),
            $container->get(Logger::class)
        );
    });

    $container->set(MessageServiceInterface::class, function ($container) {
        return new MessageService(
            $container->get(MessageRepositoryInterface::class),
            $container->get(GroupMemberRepositoryInterface::class),
            $container->get(Logger::class)
        );
    });
};
