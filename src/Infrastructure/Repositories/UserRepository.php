<?php

namespace Infrastructure\Repositories;

use Domain\Entities\User;
use Domain\Repositories\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function save(User $user): void {
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute([':username' => $user->getUsername(), ':password' => $user->getPassword()]);

        // Set the ID on the User entity after insertion
        $user->setId((int) $this->db->lastInsertId());
    }

    public function findByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? new User($result['id'], $result['username'], $result['password']) : null;
    }
}
