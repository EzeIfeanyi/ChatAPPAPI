<?php

namespace Infrastructure\Repositories;

use Domain\Entities\Group;
use Application\Repositories\GroupRepositoryInterface;
use PDO;

class GroupRepository implements GroupRepositoryInterface
{
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Group $group): void
    {
        $stmt = $this->connection->prepare("INSERT INTO groups (name) VALUES (:name)");
        $stmt->bindParam(':name', $group->getName());
        $stmt->execute();
        $group->setId((int) $this->connection->lastInsertId());
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM groups");
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($group) {
            return new Group($group['id'], $group['name']);
        }, $groups);
    }

    public function findGroupsByUserId(int $userId): array
    {
        $stmt = $this->connection->prepare("
            SELECT g.* FROM groups g
            JOIN group_members gm ON g.id = gm.group_id
            WHERE gm.user_id = :userId
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($group) {
            return new Group($group['id'], $group['name']);
        }, $groups);
    }

    public function groupExists(int $groupId): bool
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM groups WHERE id = :groupId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function groupNameExists(string $name): bool
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM groups WHERE name = :name");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
