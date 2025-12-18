<?php

class DatabaseConnectionFactory {
    public static function createConnection(): PDO {
        // STRICT MODE: Always require all database settings
        $host = getenv('DB_HOST') ?: throw new RuntimeException('DB_HOST is required');
        $port = getenv('DB_PORT') ?: throw new RuntimeException('DB_PORT is required');
        $db   = getenv('DB_NAME') ?: throw new RuntimeException('DB_NAME is required');
        $user = getenv('DB_USER') ?: throw new RuntimeException('DB_USER is required');
        $pass = getenv('DB_PASS') ?: '';  // Password can be empty for XAMPP

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, $user, $pass, $options);
    }
}


