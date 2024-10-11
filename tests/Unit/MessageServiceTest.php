<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Application\Services\MessageService;
use Domain\Repositories\MessageRepositoryInterface;
use Domain\Repositories\GroupMemberRepositoryInterface;
use Application\DTOs\MessageDTO;
use Domain\Entities\Message;
use Monolog\Logger;

class MessageServiceTest extends TestCase
{
    private $messageRepository;
    private $groupMemberRepository;
    private $logger;
    private $messageService;

    public function setUp(): void
    {
        // Mock the repositories and logger
        $this->messageRepository = $this->createMock(MessageRepositoryInterface::class);
        $this->groupMemberRepository = $this->createMock(GroupMemberRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);

        // Initialize the service with mocked dependencies
        $this->messageService = new MessageService($this->messageRepository, $this->groupMemberRepository, $this->logger);
    }

    public function testSendMessageSuccessfully()
    {
        $messageDTO = new MessageDTO();
        $messageDTO->groupId = 1;
        $messageDTO->userId = 1;
        $messageDTO->content = 'Hello world';

        // Expect the group member check to pass
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(true);

        // Expect the message to be saved in the repository
        $this->messageRepository->expects($this->once())
            ->method('save')
            ->willReturn(new Message(1, 1, 1, 'Hello world'));

        // Expect logger to be called on sending attempt and success
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['User sending message', ['user_id' => 1, 'group_id' => 1, 'content' => 'Hello world']],
                ['Message sent successfully', ['message_id' => 1, 'user_id' => 1, 'group_id' => 1]]
            );

        // Call the sendMessage method
        $message = $this->messageService->sendMessage($messageDTO);

        // Verify the returned message
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Hello world', $message->getContent());
    }

    public function testSendMessageUserNotMemberOfGroup()
    {
        $messageDTO = new MessageDTO();
        $messageDTO->groupId = 1;
        $messageDTO->userId = 1;
        $messageDTO->content = 'Hello world';

        // Expect the group member check to fail
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(false);

        // Expect logger to log the failed attempt
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('User attempted to send a message but is not a member of the group', [
                'user_id' => 1,
                'group_id' => 1
            ]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not a member of this group.');

        // Call the sendMessage method, which should throw an exception
        $this->messageService->sendMessage($messageDTO);
    }

    public function testGetMessagesByGroupSuccessfully()
    {
        $groupId = 1;
        $userId = 1;
        $messages = [
            new Message(1, 1, 1, 'Hello world'),
            new Message(2, 1, 1, 'Second message')
        ];

        // Expect the group member check to pass
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with($userId, $groupId)
            ->willReturn(true);

        // Expect the repository to return a list of messages
        $this->messageRepository->expects($this->once())
            ->method('findByGroupId')
            ->with($groupId)
            ->willReturn($messages);

        // Expect logger to be called on retrieval attempt and success
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['User retrieving messages', ['user_id' => $userId, 'group_id' => $groupId]],
                ['Messages retrieved successfully', [
                    'user_id' => $userId, 
                    'group_id' => $groupId, 
                    'message_count' => count($messages)
                ]]
            );

        // Call the getMessagesByGroup method
        $result = $this->messageService->getMessagesByGroup($groupId, $userId);

        // Verify the messages
        $this->assertCount(2, $result);
        $this->assertEquals('Hello world', $result[0]->getContent());
        $this->assertEquals('Second message', $result[1]->getContent());
    }

    public function testGetMessagesByGroupUserNotMemberOfGroup()
    {
        $groupId = 1;
        $userId = 1;

        // Expect the group member check to fail
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with($userId, $groupId)
            ->willReturn(false);

        // Expect logger to log the failed attempt
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('User attempted to retrieve messages but is not a member of the group', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is not a member of this group.');

        // Call the getMessagesByGroup method, which should throw an exception
        $this->messageService->getMessagesByGroup($groupId, $userId);
    }
}
