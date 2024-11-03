<?php
namespace Application\Repositories;

use Domain\Entities\GroupMember;

interface GroupMemberRepositoryInterface {
    public function addMember(int $userId, int $groupId): void;
    public function isUserMemberOfGroup(int $userId, int $groupId): bool;
}
