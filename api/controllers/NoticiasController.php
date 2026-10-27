<?php
require_once __DIR__ . '/../models/Noticia.php';

/**
 * Controlador para Noticias
 */
class NoticiasController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new Noticia($db);
    }

    /**
     * Lista todas las noticias activas
     */
    public function listar() {
        $noticias = $this->modelo->obtenerActivas();
        return json_encode($noticias);
    }

    /**
     * Obtiene una noticia específica
     */
    public function obtener($id) {
        $noticia = $this->modelo->obtenerPorId($id);
        if ($noticia) {
            return json_encode($noticia);
        } else {
            http_response_code(404);
            return json_encode(["error" => "Noticia no encontrada"]);
        }
    }

    /**
     * Crea una nueva noticia
     */
    public function crear($datos) {
        if (!isset($datos['titulo']) || empty($datos['titulo']) || 
            !isset($datos['contenido']) || empty($datos['contenido'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos título y contenido son requeridos"]);
        }

        $id = $this->modelo->crear($datos);
        if ($id) {
            http_response_code(201);
            return json_encode([
                "mensaje" => "Noticia creada exitosamente",
                "id_noticia" => $id
            ]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo crear la noticia"]);
        }
    }

    /**
     * Actualiza una noticia
     */
    public function actualizar($id, $datos) {
        if (!isset($datos['titulo']) || empty($datos['titulo']) || 
            !isset($datos['contenido']) || empty($datos['contenido'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos título y contenido son requeridos"]);
        }

        if ($this->modelo->actualizar($id, $datos)) {
            return json_encode(["mensaje" => "Noticia actualizada exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo actualizar la noticia"]);
        }
    }

    /**
     * Cambia el estado de una noticia
     */
    public function cambiarEstado($id, $estado) {
        if ($this->modelo->cambiarEstado($id, $estado)) {
            return json_encode(["mensaje" => "Estado actualizado exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo actualizar el estado"]);
        }
    }

    /**
     * Elimina una noticia
     */
    public function eliminar($id) {
        if ($this->modelo->eliminar($id)) {
            return json_encode(["mensaje" => "Noticia eliminada exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo eliminar la noticia"]);
        }
    }
}
