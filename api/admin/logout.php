<?php
/**
 * Endpoint para cerrar sesión de administrador
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use POST."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$controller = new AdminController($db);
echo $controller->logout();
