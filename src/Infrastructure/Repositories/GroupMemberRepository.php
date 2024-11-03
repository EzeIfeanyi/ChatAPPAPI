<?php
namespace Infrastructure\Repositories;

use Application\Repositories\GroupMemberRepositoryInterface;
use PDO;

class GroupMemberRepository implements GroupMemberRepositoryInterface {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function addMember(int $userId, int $groupId): void {
        if ($this->isUserMemberOfGroup($userId, $groupId)) {
            throw new \Exception('User is already a member of this group.');
        }

        $stmt = $this->db->prepare("INSERT INTO group_members (user_id, group_id) VALUES (:userId, :groupId)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function isUserMemberOfGroup(int $userId, int $groupId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM group_members 
            WHERE user_id = :userId AND group_id = :groupId
        ");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
