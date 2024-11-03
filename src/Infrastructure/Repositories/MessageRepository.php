<?php
namespace Infrastructure\Repositories;

use Application\Repositories\MessageRepositoryInterface;
use Domain\Entities\Message;
use PDO;

class MessageRepository implements MessageRepositoryInterface {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function save(Message $message): void {
        $stmt = $this->db->prepare("INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)");
        $stmt->execute([':group_id' => $message->getGroupId(), ':user_id' => $message->getUserId(), ':content' => $message->getContent()]);
        $message->setId((int) $this->db->lastInsertId());
    }

    public function findByGroupId(int $groupId): array {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE group_id = :group_id ORDER BY id ASC");
        $stmt->execute([':group_id' => $groupId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $messages = [];
        foreach ($results as $row) {
            $messages[] = new Message($row['id'], $row['group_id'], $row['user_id'], $row['content']);
        }
        return $messages;
    }
}
