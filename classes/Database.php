<?php

namespace classes;

use PDO;
use PDOException;

class Database
{
    private $host = 'localhost';
    private $db_name = 'ppdb';
    private $username = 'root';
    private $password = 'root';
    private $conn;

    // Constructor
    public function __construct()
    {
        $this->connect();
    }

    // Method to connect to the database
    private function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
