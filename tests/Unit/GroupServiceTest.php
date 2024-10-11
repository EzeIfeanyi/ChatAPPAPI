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
        // Mock the repositories and logger
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->groupMemberRepository = $this->createMock(GroupMemberRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);

        // Initialize the service with mocked dependencies
        $this->groupService = new GroupService($this->groupRepository, $this->groupMemberRepository, $this->logger);
    }

    public function testCreateGroupSuccessfully()
    {
        $groupDTO = new GroupDTO();
        $groupDTO->name = 'Test Group';

        // Expect repository check for group name
        $this->groupRepository->expects($this->once())
            ->method('groupNameExists')
            ->with('Test Group')
            ->willReturn(false);

        // Expect repository to save the group
        $this->groupRepository->expects($this->once())
            ->method('save')
            ->willReturn(new Group(1, 'Test Group'));

        // Expect the group creator to be added as a member
        $this->groupMemberRepository->expects($this->once())
            ->method('addMember')
            ->with(1, 1);

        // Expect logger to log group creation attempts and success
        $this->logger->expects($this->exactly(3))
            ->method('info')
            ->withConsecutive(
                ['Group creation attempt', ['group_name' => 'Test Group', 'user_id' => 1]],
                ['Group created successfully', ['group_id' => 1, 'group_name' => 'Test Group', 'user_id' => 1]],
                ['User joined group successfully', ['group_id' => 1, 'user_id' => 1]]
            );

        // Call createGroup and assert
        $group = $this->groupService->createGroup($groupDTO, 1);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('Test Group', $group->getName());
    }

    public function testCreateGroupFailsWhenNameAlreadyExists()
    {
        $groupDTO = new GroupDTO();
        $groupDTO->name = 'Existing Group';

        // Expect repository to check and find existing group name
        $this->groupRepository->expects($this->once())
            ->method('groupNameExists')
            ->with('Existing Group')
            ->willReturn(true);

        // Expect logger to log the failure
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Failed to create group: Group name already exists', ['group_name' => 'Existing Group', 'user_id' => 1]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A group with this name already exists.');

        // Call createGroup and expect failure
        $this->groupService->createGroup($groupDTO, 1);
    }

    public function testJoinGroupSuccessfully()
    {
        // Expect repository to check if group exists
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(true);

        // Expect repository to check if user is already a member
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(false);

        // Expect user to be added as a member
        $this->groupMemberRepository->expects($this->once())
            ->method('addMember')
            ->with(1, 1);

        // Expect logger to log the join attempt and success
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['User join group attempt', ['user_id' => 1, 'group_id' => 1]],
                ['User joined group successfully', ['group_id' => 1, 'user_id' => 1]]
            );

        // Call joinGroup and assert
        $this->groupService->joinGroup(1, 1);

        $this->assertTrue(true); // If no exceptions, the test passes
    }

    public function testJoinGroupFailsWhenGroupDoesNotExist()
    {
        // Expect repository to check and find the group does not exist
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(false);

        // Expect logger to log the failure
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Failed to join group: Group does not exist', ['group_id' => 1, 'user_id' => 1]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Group does not exist.');

        // Call joinGroup and expect failure
        $this->groupService->joinGroup(1, 1);
    }

    public function testJoinGroupFailsWhenUserIsAlreadyAMember()
    {
        // Expect repository to check if group exists
        $this->groupRepository->expects($this->once())
            ->method('groupExists')
            ->with(1)
            ->willReturn(true);

        // Expect repository to check if user is already a member
        $this->groupMemberRepository->expects($this->once())
            ->method('isUserMemberOfGroup')
            ->with(1, 1)
            ->willReturn(true);

        // Expect logger to log the failure
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Failed to join group: User is already a member', ['group_id' => 1, 'user_id' => 1]);

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is already a member of this group.');

        // Call joinGroup and expect failure
        $this->groupService->joinGroup(1, 1);
    }
}
