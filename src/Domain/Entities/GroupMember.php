<?php
namespace Domain\Entities;

class GroupMember {
    private $id;
    private $groupId;
    private $userId;

    public function __construct($id, $groupId, $userId) {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->userId = $userId;
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
}
