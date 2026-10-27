<?php
/**
 * Endpoint para eliminar un documento
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';

// Requerir autenticación
requireAuth();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/DocumentosController.php';

// Solo aceptar peticiones DELETE o POST con id
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use DELETE o POST."]);
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    $json = file_get_contents('php://input');
    $datos = json_decode($json, true);
    if (isset($datos['id'])) $id = (int)$datos['id'];
}

if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "El ID del documento es requerido"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$controller = new DocumentosController($db);
echo $controller->eliminar($id);
