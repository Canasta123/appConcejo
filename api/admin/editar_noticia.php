<?php
/**
 * Endpoint para editar una noticia
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/session.php';

// Requerir autenticación
requireAuth();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NoticiasController.php';

// Solo aceptar peticiones PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use PUT."]);
    exit();
}

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "El ID de la noticia es requerido"]);
    exit();
}

$id = (int)$_GET['id'];

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
echo $controller->actualizar($id, $datos);
