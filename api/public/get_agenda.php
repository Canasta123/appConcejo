<?php
/**
 * Endpoint público para obtener la agenda de eventos
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AgendaController.php';

// Solo aceptar peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido. Use GET."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$controller = new AgendaController($db);

// Leer mes y año de $_GET, por defecto mes y año actual
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

echo $controller->listarPorMes($mes, $anio);
