<?php
require_once __DIR__ . '/../models/AdminUsuario.php';

/**
 * Controlador para la Administración (Autenticación)
 */
class AdminController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new AdminUsuario($db);
    }

    /**
     * Inicia sesión para un administrador
     */
    public function login($datos) {
        if (!isset($datos['usuario']) || empty($datos['usuario']) || 
            !isset($datos['password']) || empty($datos['password'])) {
            http_response_code(400);
            return json_encode(["error" => "Usuario y contraseña son requeridos"]);
        }

        $admin = $this->modelo->autenticar($datos['usuario'], $datos['password']);

        if ($admin) {
            // Iniciar sesión (debería llamarse a session_start() en session.php)
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_usuario'] = $admin['usuario'];
            $_SESSION['admin_rol'] = $admin['rol'];

            return json_encode([
                "mensaje" => "Inicio de sesión exitoso",
                "admin" => $admin
            ]);
        } else {
            http_response_code(401);
            return json_encode(["error" => "Credenciales incorrectas"]);
        }
    }

    /**
     * Cierra la sesión del administrador
     */
    public function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();

        // Borrar la cookie de sesión si existe
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir la sesión
        session_destroy();

        return json_encode(["mensaje" => "Sesión cerrada exitosamente"]);
    }

    /**
     * Verifica si hay una sesión activa y devuelve sus datos
     */
    public function verificarSesion() {
        $admin = getCurrentAdmin(); // Función de session.php
        
        if ($admin) {
            return json_encode([
                "autenticado" => true,
                "admin" => $admin
            ]);
        } else {
            http_response_code(401);
            return json_encode([
                "autenticado" => false,
                "error" => "No hay sesión activa"
            ]);
        }
    }
}
