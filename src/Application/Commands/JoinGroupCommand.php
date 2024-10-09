<?php

namespace Application\Commands;

use Application\Services\GroupService;
use Application\Services\GroupServiceInterface;

class JoinGroupCommand {
    private $groupService;

    public function __construct(GroupServiceInterface $groupService) {
        $this->groupService = $groupService;
    }

    public function execute(int $userId, int $groupId) {
        return $this->groupService->joinGroup($userId, $groupId);
    }
}
