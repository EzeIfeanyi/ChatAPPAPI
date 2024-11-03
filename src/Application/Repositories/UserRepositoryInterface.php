<?php

namespace Application\Repositories;

use Domain\Entities\User;

interface UserRepositoryInterface {
    public function save(User $user): void;
    public function findByUsername(string $username): ?User;
}
