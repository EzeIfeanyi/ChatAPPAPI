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
        $this->messageRepository = $this->createMock(MessageRepositoryInterface::class);
        $this->groupMemberRepository = $this->createMock(GroupMemberRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);

        $this->messageService = new MessageService($this->messageRepository, $this->groupMemberRepository, $this->logger);
    }

    public function testSendMessageSuccessfully()
    {
        $messageDTO = new MessageDTO();
        $messageDTO->groupId = 1;
        $messageDTO->userId = 1;
        $messageDTO->content = 'Hello world';

        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(true);

        $this->messageRepository->expects($this->once())
            ->method('save')
            ->willReturn(new Message(1, 1, 1, 'Hello world'));

        $message = $this->messageService->sendMessage($messageDTO);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Hello world', $message->getContent());
    }
}

