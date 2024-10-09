<?php

namespace Application\Services;

use Domain\Entities\User;
use Application\DTOs\UserDTO;

interface UserServiceInterface {
    public function register(UserDTO $userDTO): User;
    public function login(string $username, string $password): ?array;
}
