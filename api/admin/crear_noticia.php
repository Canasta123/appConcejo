<?php
/**
 * Endpoint para crear una noticia
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';

// Requerir autenticación
requireAuth();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NoticiasController.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use POST."]);
    exit();
}

// Leer cuerpo de la petición JSON
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

if ($datos === null) {
    http_response_code(400);
    echo json_encode(["error" => "JSON inválido"]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$controller = new NoticiasController($db);
echo $controller->crear($datos);
