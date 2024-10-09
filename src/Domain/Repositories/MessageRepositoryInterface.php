<?php

namespace Domain\Repositories;

use Domain\Entities\Message;

interface MessageRepositoryInterface {
    public function save(Message $message): void;
    public function findByGroupId(int $groupId): array;
}
