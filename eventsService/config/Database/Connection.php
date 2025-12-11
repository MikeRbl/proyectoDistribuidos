<?php

namespace Config\Database;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Connection {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Cargar variables de entorno
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        try {
            // String de conexión para PostgreSQL
            $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            
            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (PDOException $e) {
            die("Error de conexión a Postgres: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Connection();
        }
        return self::$instance->conn;
    }
}