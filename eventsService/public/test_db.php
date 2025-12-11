<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Config\Database\Connection;

try {
    $db = Connection::getInstance();
    echo json_encode(["message" => "Conexión exitosa a PostgreSQL desde PHP!"]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}