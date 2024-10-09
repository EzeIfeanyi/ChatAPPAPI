<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Application\Services\GroupService;
use Domain\Repositories\GroupRepositoryInterface;
use Domain\Repositories\GroupMemberRepositoryInterface;
use Application\DTOs\GroupDTO;
use Domain\Entities\Group;
use Monolog\Logger;

class GroupServiceTest extends TestCase
{
    private $groupRepository;
    private $groupMemberRepository;
    private $logger;
    private $groupService;

    public function setUp(): void
    {
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->groupMemberRepository = $this->createMock(GroupMemberRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);

        $this->groupService = new GroupService($this->groupRepository, $this->groupMemberRepository, $this->logger);
    }

    public function testCreateGroupSuccessfully()
    {
        $groupDTO = new GroupDTO();
        $groupDTO->name = 'Test Group';

        $this->groupRepository->expects($this->once())
            ->method('groupNameExists')
            ->with('Test Group')
            ->willReturn(false);

        $this->groupRepository->expects($this->once())
            ->method('save')
            ->willReturn(new Group(1, 'Test Group'));

        $this->groupMemberRepository->expects($this->once())
            ->method('addMember')
            ->with(1, 1);

        $group = $this->groupService->createGroup($groupDTO, 1);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('Test Group', $group->getName());
    }

    public function testCreateGroupFailsWhenNameAlreadyExists()
    {
        $groupDTO = new GroupDTO();
        $groupDTO->name = 'Existing Group';

        $this->groupRepository->expects($this->once())
            ->method('groupNameExists')
            ->with('Existing Group')
            ->willReturn(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A group with this name already exists.');

        $this->groupService->createGroup($groupDTO, 1);
    }

    public function testJoinGroupSuccessfully()
    {
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(true);

        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(false);

        $this->groupMemberRepository->expects($this->once())
            ->method('addMember')
            ->with(1, 1);

        $this->groupService->joinGroup(1, 1);

        $this->assertTrue(true); // If no exceptions were thrown, the test passes
    }

    public function testJoinGroupFailsWhenGroupDoesNotExist()
    {
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Group does not exist.');

        $this->groupService->joinGroup(1, 1);
    }

    public function testJoinGroupFailsWhenUserIsAlreadyAMember()
    {
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(true);

        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is already a member of this group.');

        $this->groupService->joinGroup(1, 1);
    }
}
