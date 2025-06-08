<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class Database
 * Handles database connections using PDO.
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Private constructor to prevent direct creation of object.
     */
    private function __construct() {}

    /**
     * Gets the PDO database connection instance (Singleton pattern).
     *
     * @return PDO The PDO instance.
     * @throws PDOException If connection fails.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                self::$config = require __DIR__ . '/../config/config.php';
            }
            $dbConfig = self::$config['db'];

            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$instance = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
        }
        return self::$instance;
    }
}