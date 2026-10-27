<?php
/**
 * Endpoint para verificar el estado de la sesión de administrador
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';

// Obtener datos del administrador desde la sesión
$admin = getCurrentAdmin();

if ($admin) {
    echo json_encode([
        "autenticado" => true,
        "admin" => $admin
    ]);
} else {
    http_response_code(200); // 200 para no generar un error 401 feo en consola JS
    echo json_encode([
        "autenticado" => false
    ]);
}
