<?php

namespace Application\Services;

use Application\DTOs\GroupDTO;
use Domain\Entities\Group;

interface GroupServiceInterface {
    public function createGroup(GroupDTO $groupDTO, int $user_id): Group;
    public function joinGroup(int $userId, int $groupId): void;
}
