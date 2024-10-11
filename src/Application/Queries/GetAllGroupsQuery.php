<?php

namespace Application\Queries;

use Application\Services\GroupServiceInterface;

class GetAllGroupsQuery
{
    private $groupService;

    public function __construct(GroupServiceInterface $groupService)
    {
        $this->groupService = $groupService;
    }

    public function execute()
    {
        return $this->groupService->getAllGroups();
    }
}
