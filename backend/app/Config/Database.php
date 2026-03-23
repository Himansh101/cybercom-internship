<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database configuration and singleton PDO connection.
 * Reads credentials from environment variables.
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns the singleton PDO instance, creating it if necessary.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host   = $_ENV['MYSQL_HOST'] ?? 'mysql';
            $port   = $_ENV['MYSQL_PORT'] ?? '3306';
            $dbname = $_ENV['MYSQL_DATABASE'] ?? 'reporting_db';
            $user   = $_ENV['MYSQL_USER'] ?? 'report_user';
            $pass   = $_ENV['MYSQL_PASSWORD'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'data'    => null,
                    'error'   => 'Database connection failed: ' . $e->getMessage(),
                    'meta'    => [],
                ]);
                exit;
            }
        }

        return self::$instance;
    }

    /** Prevent instantiation */
    private function __construct() {}
}
