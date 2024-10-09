<?php

namespace Application\Services;

use Application\DTOs\MessageDTO;
use Domain\Entities\Message;

interface MessageServiceInterface {
    public function sendMessage(MessageDTO $messageDTO): Message;
    public function getMessagesByGroup(int $groupId, int $userId): array;
}
