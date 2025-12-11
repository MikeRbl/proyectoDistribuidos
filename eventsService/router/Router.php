<?php
namespace Router;

use App\Controllers\EventController;
use Config\Tools\JwtHelper;

class Router {
    private $routes = [];

    public function __construct() {
        $this->initializeRoutes();
    }

    private function initializeRoutes() {
        // Estructura: 'RUTA' => ['Clase', 'NombreMetodo', ¿EsPrivada?]
        
        $this->routes['GET'] = [
            '/events' => [EventController::class, 'list', true],      // Listar (Privado)
            '/events/get' => [EventController::class, 'getById', true] // Obtener uno (Privado)
        ];

        $this->routes['POST'] = [
            '/events' => [EventController::class, 'create', true]     // Crear (Privado)
        ];

        $this->routes['PUT'] = [
            '/events' => [EventController::class, 'update', true]     // Actualizar (Privado)
        ];

        $this->routes['DELETE'] = [
            '/events' => [EventController::class, 'delete', true]     // Eliminar (Privado)
        ];
    }

    public function handleRequest() {
        // 1. Obtener método y URI
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Limpiar URI (Quitar query params como ?id=1 y prefijos si usas subcarpetas)
        // Ejemplo: "/api/events?id=1" se convierte en "/events" si tu .htaccess o index.php manejan el /api
        $uri = strtok($uri, '?'); 
        $uri = str_replace('/api', '', $uri); // Quitamos /api si lo usas en el prefijo

        // 2. Verificar si la ruta existe
        if (isset($this->routes[$method][$uri])) {
            $routeConfig = $this->routes[$method][$uri];
            $controllerClass = $routeConfig[0];
            $action = $routeConfig[1];
            $isProtected = $routeConfig[2];

            // 3. Middleware de Seguridad (JWT)
            if ($isProtected) {
                $jwt = new JwtHelper();
                $userPayload = $jwt->validateToken();

                if (!$userPayload) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Acceso denegado. Token inválido o expirado.']);
                    return;
                }
                
                // (Opcional) Puedes guardar el ID del usuario en una variable global o pasarlo
                // $_REQUEST['user_id'] = $userPayload->id;
            }

            // 4. Instanciar controlador y ejecutar método
            $controller = new $controllerClass();
            
            // Si el método recibe parámetros por URL (GET), pasarlos
            if (isset($_GET['id'])) {
                $controller->$action($_GET['id']);
            } else {
                $controller->$action();
            }

        } else {
            // Ruta no encontrada
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado: ' . $uri]);
        }
    }
}