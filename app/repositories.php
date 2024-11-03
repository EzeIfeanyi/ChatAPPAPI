<?php

use DI\Container;
use Application\Repositories\UserRepositoryInterface;
use Application\Repositories\GroupRepositoryInterface;
use Application\Repositories\GroupMemberRepositoryInterface;
use Application\Repositories\MessageRepositoryInterface;
use Infrastructure\Repositories\UserRepository;
use Infrastructure\Repositories\GroupRepository;
use Infrastructure\Repositories\GroupMemberRepository;
use Infrastructure\Repositories\MessageRepository;
use Infrastructure\Database\DatabaseConnectionInterface;

return function (Container $container) {
    // Register repositories
    $container->set(UserRepositoryInterface::class, function ($container) {
        $dbConnection = $container->get(DatabaseConnectionInterface::class)->getConnection();
        return new UserRepository($dbConnection);
    });

    $container->set(GroupRepositoryInterface::class, function ($container) {
        $dbConnection = $container->get(DatabaseConnectionInterface::class)->getConnection();
        return new GroupRepository($dbConnection);
    });

    $container->set(GroupMemberRepositoryInterface::class, function ($container) {
        $dbConnection = $container->get(DatabaseConnectionInterface::class)->getConnection();
        return new GroupMemberRepository($dbConnection);
    });

    $container->set(MessageRepositoryInterface::class, function ($container) {
        $dbConnection = $container->get(DatabaseConnectionInterface::class)->getConnection();
        return new MessageRepository($dbConnection);
    });
};
