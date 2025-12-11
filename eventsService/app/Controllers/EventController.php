<?php
namespace App\Controllers;

use App\Models\EventModel;
use Config\Tools\JwtHelper;

class EventController {
    private $model;
    private $jwt;

    public function __construct() {
        $this->model = new EventModel();
        $this->jwt = new JwtHelper();
    }

    public function list() {
        // Validar Token (Opcional si quieres la lista pública, pero el PDF pide sesión iniciada)
        $user = $this->jwt->validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $events = $this->model->getAll();
        echo json_encode($events);
    }

    public function create() {
        // 1. Validar Token y obtener ID del usuario
        $user = $this->jwt->validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado. Token inválido o ausente.']);
            return;
        }

        // 2. Obtener datos del Body (JSON)
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['nombre_evento']) || !isset($data['fecha_evento'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos obligatorios']);
            return;
        }

        // 3. Agregar el ID del usuario al array de datos
        $data['usuario_registro'] = $user->id; 

        // 4. Guardar en BD
        try {
            $newId = $this->model->create($data);
            http_response_code(201);
            echo json_encode(['message' => 'Evento creado', 'id' => $newId]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear evento: ' . $e->getMessage()]);
        }
    }

    // Obtener un solo evento por ID
    public function getById($id = null) {
        $user = $this->jwt->validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro ID']);
            return;
        }

        $event = $this->model->getById($id);
        
        if ($event) {
            echo json_encode($event);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Evento no encontrado']);
        }
    }

    // Actualizar un evento
    public function update($id = null) {
        // 1. Validar Token
        $user = $this->jwt->validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        // 2. Validar ID
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el ID del evento a editar']);
            return;
        }

        // 3. Validar si el evento existe y pertenece al usuario (Opcional según reglas de negocio)
        $event = $this->model->getById($id);
        if (!$event) {
            http_response_code(404);
            echo json_encode(['error' => 'Evento no encontrado']);
            return;
        }

        // 4. Leer datos nuevos
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // 5. Actualizar en Base de Datos
        // NOTA: Necesitamos agregar el método update en el Modelo (Paso 3)
        try {
            $this->model->update($id, $data);
            echo json_encode(['message' => 'Evento actualizado correctamente']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    // Eliminar un evento
    public function delete($id = null) {
        $user = $this->jwt->validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta el ID']);
            return;
        }

        // Verificar existencia antes de borrar
        $event = $this->model->getById($id);
        if (!$event) {
            http_response_code(404);
            echo json_encode(['error' => 'Evento no encontrado']);
            return;
        }

        try {
            $this->model->delete($id);
            echo json_encode(['message' => 'Evento eliminado']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}