<?php

namespace Infrastructure\Database;

use PDO;
use PDOException;

class SQLiteConnection implements DatabaseConnectionInterface {
    private PDO $connection;

    public function __construct(string $dbFile) {
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }

        try {
            $this->connection = new PDO('sqlite:' . $dbFile);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \RuntimeException("Could not connect to the database: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
