<?php
namespace Application\Validators;

use Application\DTOs\MessageDTO;
use Exception;

class MessageValidator {
    public static function validate(MessageDTO $messageDTO) {
        if (empty($messageDTO->content)) {
            throw new Exception("Message content cannot be empty.");
        }

        if (empty($messageDTO->groupId) || empty($messageDTO->userId)) {
            throw new Exception("Both group ID and user ID are required.");
        }
    }
}
