<?php

namespace Domain\Repositories;

use Domain\Entities\Group;

interface GroupRepositoryInterface {
    public function save(Group $group): void;
    public function findAll(): array;
    public function groupExists(int $groupId): bool;
    public function groupNameExists(string $name): bool;
}
