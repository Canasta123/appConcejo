<?php
/**
 * Configuración de sesiones para autenticación de administradores
 */

// Configuración segura de sesión
session_name('concejo_admin_session');
session_set_cookie_params([
    'lifetime' => 86400, // 24 horas
    'path' => '/',
    'domain' => '', 
    'secure' => isset($_SERVER['HTTPS']), // true si se usa HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el administrador ha iniciado sesión
 * Si no, retorna código 401 y finaliza la ejecución
 */
function requireAuth() {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "No autorizado. Inicie sesión."]);
        exit();
    }
}

/**
 * Obtiene los datos del administrador actual desde la sesión
 * @return array|null
 */
function getCurrentAdmin() {
    if (isset($_SESSION['admin_id'])) {
        return [
            'id_admin' => $_SESSION['admin_id'],
            'usuario' => $_SESSION['admin_usuario'],
            'rol' => $_SESSION['admin_rol'] ?? 'admin'
        ];
    }
    return null;
}
