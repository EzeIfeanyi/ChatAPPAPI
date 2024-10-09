<?php

namespace Application\Services;

use Domain\Entities\Group;
use Domain\Repositories\GroupRepositoryInterface;
use Domain\Repositories\GroupMemberRepositoryInterface;
use Application\DTOs\GroupDTO;
use Application\Validators\GroupValidator;
use Monolog\Logger;

class GroupService implements GroupServiceInterface {
    private $groupRepository;
    private $groupMemberRepository;
    private $logger;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        GroupMemberRepositoryInterface $groupMemberRepository,
        Logger $logger) {
        $this->groupRepository = $groupRepository;
        $this->groupMemberRepository = $groupMemberRepository;
        $this->logger = $logger;
    }

    public function createGroup(GroupDTO $groupDTO, int $user_id): Group {
        GroupValidator::validate($groupDTO);

        // Log the group creation attempt
        $this->logger->info('Group creation attempt', [
            'group_name' => $groupDTO->name,
            'user_id' => $user_id,
        ]);

        // Check if a group with the same name already exists
        if ($this->groupRepository->groupNameExists($groupDTO->name)) {
            // Log the failure to create group due to existing group name
            $this->logger->warning('Failed to create group: Group name already exists', [
                'group_name' => $groupDTO->name,
                'user_id' => $user_id,
            ]);
            throw new \Exception('A group with this name already exists.');
        }

        $group = new Group(null, $groupDTO->name);
        $this->groupRepository->save($group);

        // Automatically add the group creator as a member of the group
        $this->joinGroup($user_id, $group->getId());

        // Log successful group creation
        $this->logger->info('Group created successfully', [
            'group_id' => $group->getId(),
            'group_name' => $groupDTO->name,
            'user_id' => $user_id,
        ]);

        return $group;
    }

    public function joinGroup(int $userId, int $groupId): void {
        // Log the join group attempt
        $this->logger->info('User join group attempt', [
            'user_id' => $userId,
            'group_id' => $groupId,
        ]);

        // Check if the group exists
        if (!$this->groupRepository->groupExists($groupId)) {
            // Log failure to join group due to non-existent group
            $this->logger->warning('Failed to join group: Group does not exist', [
                'group_id' => $groupId,
                'user_id' => $userId,
            ]);
            throw new \Exception('Group does not exist.');
        }
        
        // Check if the user is already a member of the group
        if ($this->groupMemberRepository->isUserMemberOfGroup($userId, $groupId)) {
            // Log failure to join group due to existing membership
            $this->logger->warning('Failed to join group: User is already a member', [
                'group_id' => $groupId,
                'user_id' => $userId,
            ]);
            throw new \Exception('User is already a member of this group.');
        }

        $this->groupMemberRepository->addMember($userId, $groupId);
        
        // Log successful membership addition
        $this->logger->info('User joined group successfully', [
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
    }
}
