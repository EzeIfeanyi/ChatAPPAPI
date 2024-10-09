<?php

namespace Application\Commands;

use Application\Services\UserServiceInterface;

class LoginUserCommand
{
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function execute(string $username, string $password): ?array {
        return $this->userService->login($username, $password);
    }
}
