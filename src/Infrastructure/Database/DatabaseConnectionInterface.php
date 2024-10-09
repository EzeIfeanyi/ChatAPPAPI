<?php

namespace Infrastructure\Database;

interface DatabaseConnectionInterface {
    public function getConnection(): \PDO;
}
