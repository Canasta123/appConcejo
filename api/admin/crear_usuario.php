<?php
/**
 * Script utilitario para crear o actualizar un usuario administrador seguro
 * Uso: Acceder desde el navegador con parámetros o enviar POST JSON:
 * https://app.concejodechia.gov.co/api/admin/crear_usuario.php?usuario=prensa_chia&password=TuClaveSegura2026!&nombre=Prensa+Concejo
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AdminUsuario.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = $_REQUEST['usuario'] ?? null;
$password = $_REQUEST['password'] ?? null;
$nombre = $_REQUEST['nombre'] ?? 'Administrador Prensa';
$rol = $_REQUEST['rol'] ?? 'admin';

if (!$usuario || !$password) {
    echo json_encode([
        "instrucciones" => "Para crear un nuevo usuario administrador, agregue ?usuario=SU_USUARIO&password=SU_PASSWORD a la URL de este archivo.",
        "ejemplo" => "/api/admin/crear_usuario.php?usuario=prensa_chia&password=MiClaveSuperSegura123!"
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Crear tabla si no existe
    $model = new AdminUsuario($db);
    $method = new ReflectionMethod('AdminUsuario', 'ensureTableExists');
    $method->setAccessible(true);
    $method->invoke($model);

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO admin_usuarios (usuario, password_hash, nombre, rol) 
              VALUES (:usuario, :hash, :nombre, :rol)
              ON DUPLICATE KEY UPDATE password_hash = :hash_update, nombre = :nombre_update, rol = :rol_update";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':hash', $hash);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':hash_update', $hash);
    $stmt->bindParam(':nombre_update', $nombre);
    $stmt->bindParam(':rol_update', $rol);
    $stmt->execute();

    echo json_encode([
        "exito" => true,
        "mensaje" => "Usuario administrador '$usuario' creado/actualizado exitosamente.",
        "usuario" => $usuario
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "exito" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
