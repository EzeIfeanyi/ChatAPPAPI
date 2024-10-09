<?php

use Slim\App;
use Presentation\Controllers\UserController;
use Presentation\Controllers\GroupController;
use Presentation\Controllers\MessageController;
use Application\Commands\RegisterUserCommand;
use Application\Commands\LoginUserCommand;
use Application\Commands\CreateGroupCommand;
use Application\Commands\JoinGroupCommand;
use Application\Commands\SendMessageCommand;
use Application\Services\GroupServiceInterface;
use Application\Services\MessageServiceInterface;
use Application\Services\UserServiceInterface;
use Monolog\Logger;
use Presentation\Middleware\AuthMiddleware;

return function (App $app) {
    // Get the services from the container
    $logger = $app->getContainer()->get(Logger::class);
    $userService = $app->getContainer()->get(UserServiceInterface::class);
    $groupService = $app->getContainer()->get(GroupServiceInterface::class);
    $messageService = $app->getContainer()->get(MessageServiceInterface::class);

    // Commands
    $registerUserCommand = new RegisterUserCommand($userService);
    $loginCommand = new LoginUserCommand($userService);
    $createGroupCommand = new CreateGroupCommand($groupService);
    $joinGroupCommand = new JoinGroupCommand($groupService);
    $sendMessageCommand = new SendMessageCommand($messageService);

    // Controllers
    $userController = new UserController($registerUserCommand, $loginCommand, $logger);
    $groupController = new GroupController($createGroupCommand, $joinGroupCommand, $logger);
    $messageController = new MessageController($sendMessageCommand, $logger);

    // Define public routes (no authentication required)
    $app->post('/register', [$userController, 'register']);
    $app->post('/login', [$userController, 'login']);

    // Define protected routes (authentication required)
    $app->group('', function ($group) use ($groupController, $messageController) {
        $group->post('/groups', [$groupController, 'create']);
        $group->post('/groups/{userId}/join/{groupId}', [$groupController, 'join']);
        $group->post('/messages', [$messageController, 'send']);
        $group->get('/groups/{groupId}/messages', [$messageController, 'getMessages']);
    })->add($app->getContainer()->get(AuthMiddleware::class));
};
