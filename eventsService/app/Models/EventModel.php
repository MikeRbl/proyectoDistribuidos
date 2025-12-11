<?php
namespace App\Models;

use Config\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class EventModel {
    private $conn;

    public function __construct() {
        $this->conn = Connection::getInstance();
    }

    // Listar todos los eventos
    public function getAll() {
        // En un caso real, aquí harías JOIN con otra tabla de usuarios si quisieras ver el nombre del creador
        // Como los usuarios están en MySQL (otro microservicio), aquí solo devolvemos el ID del creador
        $stmt = $this->conn->query("SELECT * FROM eventos ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll();
    }

    // Crear un nuevo evento
    public function create($data) {
        $id = Uuid::uuid4()->toString(); // Generamos ID único
        
        $sql = "INSERT INTO eventos (
                    id, nombre_evento, descripcion, fecha_evento, lugar, capacidad, 
                    precio_entrada, organizador, categoria, estado, usuario_registro
                ) VALUES (
                    :id, :nombre, :desc, :fecha, :lugar, :cap, 
                    :precio, :org, :cat, :estado, :user_id
                )";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre_evento'],
            ':desc' => $data['descripcion'],
            ':fecha' => $data['fecha_evento'],
            ':lugar' => $data['lugar'],
            ':cap' => $data['capacidad'],
            ':precio' => $data['precio_entrada'],
            ':org' => $data['organizador'],
            ':cat' => $data['categoria'],
            ':estado' => 'Activo', // Por defecto
            ':user_id' => $data['usuario_registro'] // Viene del Token
        ]);

        return $id;
    }

    public function update($id, $data) {
        // Construimos la consulta dinámicamente según qué campos vengan
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre_evento'])) { $fields[] = "nombre_evento = :nombre"; $params[':nombre'] = $data['nombre_evento']; }
        if (isset($data['descripcion']))   { $fields[] = "descripcion = :desc";     $params[':desc'] = $data['descripcion']; }
        if (isset($data['fecha_evento']))  { $fields[] = "fecha_evento = :fecha";   $params[':fecha'] = $data['fecha_evento']; }
        if (isset($data['lugar']))         { $fields[] = "lugar = :lugar";          $params[':lugar'] = $data['lugar']; }
        if (isset($data['capacidad']))     { $fields[] = "capacidad = :cap";        $params[':cap'] = $data['capacidad']; }
        if (isset($data['precio_entrada'])){ $fields[] = "precio_entrada = :precio";$params[':precio'] = $data['precio_entrada']; }
        
        // Siempre actualizamos la fecha de actualización
        $fields[] = "fecha_actualizacion = NOW()";

        if (empty($fields)) return; // Nada que actualizar

        $sql = "UPDATE eventos SET " . implode(", ", $fields) . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    }

    // Obtener un evento por ID
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM eventos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Eliminar evento (Lógico o físico, aquí haremos físico para simplificar)
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM eventos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

}