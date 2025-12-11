<?php
namespace Config\Tools;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JwtHelper {
    private $secret;

    public function __construct() {
        // Aseguramos que las variables de entorno estén cargadas
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
        $this->secret = $_ENV['JWT_SECRET'] ?? 'secretodefault';
    }

    public function validateToken() {
        $headers = null;

        // Obtener headers (compatible con Apache y servidor interno de PHP)
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $token = $matches[1];
                try {
                    // Decodificar el token usando el mismo secreto que Node.js
                    $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
                    return $decoded; // Devuelve los datos del usuario (id, email)
                } catch (\Exception $e) {
                    return null; // Token inválido o expirado
                }
            }
        }
        return null; // No se encontró token
    }
}