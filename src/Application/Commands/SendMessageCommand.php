<?php
namespace Application\Commands;

use Application\DTOs\MessageDTO;
use Application\Services\MessageServiceInterface;

class SendMessageCommand {
    private $messageService;

    public function __construct(MessageServiceInterface $messageService) {
        $this->messageService = $messageService;
    }

    public function execute(MessageDTO $messageDTO) {
        return $this->messageService->sendMessage($messageDTO);
    }

    public function getMessagesByGroup(int $groupId, int $userId): array {
        return $this->messageService->getMessagesByGroup($groupId, $userId);
    }
}
