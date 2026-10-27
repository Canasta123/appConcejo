<?php
require_once __DIR__ . '/../models/Documento.php';

/**
 * Controlador para Documentos
 */
class DocumentosController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new Documento($db);
    }

    /**
     * Lista los documentos (opcionalmente filtrados por categoría)
     */
    public function listar($categoria = null) {
        if ($categoria) {
            $documentos = $this->modelo->obtenerPorCategoria($categoria);
        } else {
            $documentos = $this->modelo->obtenerTodos();
        }
        return json_encode($documentos);
    }

    /**
     * Obtiene un documento específico
     */
    public function obtener($id) {
        $documento = $this->modelo->obtenerPorId($id);
        if ($documento) {
            return json_encode($documento);
        } else {
            http_response_code(404);
            return json_encode(["error" => "Documento no encontrado"]);
        }
    }

    /**
     * Crea un nuevo documento
     */
    public function crear($datos) {
        if (!isset($datos['nombre']) || empty($datos['nombre']) || 
            !isset($datos['categoria']) || empty($datos['categoria']) ||
            !isset($datos['url_pdf']) || empty($datos['url_pdf'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos nombre, categoría y url_pdf son requeridos"]);
        }

        $id = $this->modelo->crear($datos);
        if ($id) {
            http_response_code(201);
            return json_encode([
                "mensaje" => "Documento creado exitosamente",
                "id_doc" => $id
            ]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo crear el documento"]);
        }
    }

    /**
     * Actualiza un documento
     */
    public function actualizar($id, $datos) {
        if (!isset($datos['nombre']) || empty($datos['nombre']) || 
            !isset($datos['categoria']) || empty($datos['categoria']) ||
            !isset($datos['url_pdf']) || empty($datos['url_pdf'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos nombre, categoría y url_pdf son requeridos"]);
        }

        if ($this->modelo->actualizar($id, $datos)) {
            return json_encode(["mensaje" => "Documento actualizado exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo actualizar el documento"]);
        }
    }

    /**
     * Elimina un documento
     */
    public function eliminar($id) {
        if ($this->modelo->eliminar($id)) {
            return json_encode(["mensaje" => "Documento eliminado exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo eliminar el documento"]);
        }
    }
}
