<?php

namespace Application\Validators;

use Application\DTOs\UserDTO;

class UserValidator {
    public static function validate(UserDTO $userDTO) {
        if (empty($userDTO->username) || empty($userDTO->password)) {
            throw new \InvalidArgumentException("Username and password are required.");
        }
    }
}
