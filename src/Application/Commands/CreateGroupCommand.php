<?php

namespace Application\Commands;

use Application\DTOs\GroupDTO;
use Application\Services\GroupServiceInterface;

class CreateGroupCommand
{
    private $groupService;

    public function __construct(GroupServiceInterface $groupService)
    {
        $this->groupService = $groupService;
    }

    public function execute(GroupDTO $groupDTO, int $user_id)
    {
        return $this->groupService->createGroup($groupDTO, $user_id);
    }
}
