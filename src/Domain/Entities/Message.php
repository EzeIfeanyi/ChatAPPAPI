<?php

namespace Domain\Entities;

class Message {
    private $id;
    private $groupId;
    private $userId;
    private $content;

    public function __construct($id, $groupId, $userId, $content) {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->content = $content;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getGroupId() {
        return $this->groupId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getContent() {
        return $this->content;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'group_id' => $this->getGroupId(),
            'user_id' => $this->getUserId(),
            'content' => $this->getContent(),
        ];
    }    
}
