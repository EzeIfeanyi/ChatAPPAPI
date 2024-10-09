<?php

namespace Application\Services;

use Domain\Repositories\MessageRepositoryInterface;
use Domain\Entities\Message;
use Application\DTOs\MessageDTO;
use Application\Validators\MessageValidator;
use Domain\Repositories\GroupMemberRepositoryInterface;
use Monolog\Logger;

class MessageService implements MessageServiceInterface {
    private $messageRepository;
    private $groupMemberRepository;
    private $logger;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        GroupMemberRepositoryInterface $groupMemberRepository,
        Logger $logger) {
        $this->messageRepository = $messageRepository;
        $this->groupMemberRepository = $groupMemberRepository;
        $this->logger = $logger;
    }

    public function sendMessage(MessageDTO $messageDTO): Message {
        MessageValidator::validate($messageDTO);

        // Check if user is a member of the group
        if (!$this->groupMemberRepository->isUserMemberOfGroup($messageDTO->userId, $messageDTO->groupId)) {
            $this->logger->warning('User attempted to send a message but is not a member of the group', [
                'user_id' => $messageDTO->userId,
                'group_id' => $messageDTO->groupId
            ]);
            throw new \Exception('User is not a member of this group.');
        }

        // Log the message sending attempt
        $this->logger->info('User sending message', [
            'user_id' => $messageDTO->userId,
            'group_id' => $messageDTO->groupId,
            'content' => $messageDTO->content
        ]);

        $message = new Message(null, $messageDTO->groupId, $messageDTO->userId, $messageDTO->content);
        $this->messageRepository->save($message);

        // Log the successful message sending
        $this->logger->info('Message sent successfully', [
            'message_id' => $message->getId(),
            'user_id' => $messageDTO->userId,
            'group_id' => $messageDTO->groupId
        ]);

        return $message;
    }

    public function getMessagesByGroup(int $groupId, int $userId): array {
        // Check if user is a member of the group
        if (!$this->groupMemberRepository->isUserMemberOfGroup($userId, $groupId)) {
            $this->logger->warning('User attempted to retrieve messages but is not a member of the group', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);
            throw new \Exception('User is not a member of this group.');
        }

        // Log the retrieval attempt
        $this->logger->info('User retrieving messages', [
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        $messages = $this->messageRepository->findByGroupId($groupId);

        // Log the successful retrieval of messages
        $this->logger->info('Messages retrieved successfully', [
            'user_id' => $userId,
            'group_id' => $groupId,
            'message_count' => count($messages),
            'messages' => $messages
        ]);

        return $messages;
    }
}
