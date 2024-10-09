<?php

namespace Application\Commands;

use Application\DTOs\UserDTO;
use Application\Services\UserServiceInterface;

class RegisterUserCommand {
    private $userService;

    public function __construct(UserServiceInterface $userService) {
        $this->userService = $userService;
    }

    public function execute(UserDTO $userDTO) {
        return $this->userService->register($userDTO);
    }
}
