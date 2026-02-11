<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    private $host = "localhost";
    private $db_name = "easycart_schema_test";
    private $username = "postgres";
    private $password = "Himanshu@2912";
    private $port = "5432";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
