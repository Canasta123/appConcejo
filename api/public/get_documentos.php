<?php
/**
 * Endpoint público para obtener documentos
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/DocumentosController.php';

// Solo aceptar peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use GET."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$controller = new DocumentosController($db);

// Leer categoría opcional de $_GET
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;

echo $controller->listar($categoria);
