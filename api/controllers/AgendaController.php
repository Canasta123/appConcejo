<?php
require_once __DIR__ . '/../models/Evento.php';

/**
 * Controlador para la Agenda de eventos
 */
class AgendaController {
    private $modelo;

    public function __construct($db) {
        $this->modelo = new Evento($db);
    }

    /**
     * Lista los eventos por mes y año
     */
    public function listarPorMes($mes, $anio) {
        $eventos = $this->modelo->obtenerPorMes($mes, $anio);
        return json_encode($eventos);
    }

    /**
     * Lista los próximos eventos
     */
    public function listarProximos() {
        $eventos = $this->modelo->obtenerProximos();
        return json_encode($eventos);
    }

    /**
     * Obtiene un evento específico
     */
    public function obtener($id) {
        $evento = $this->modelo->obtenerPorId($id);
        if ($evento) {
            return json_encode($evento);
        } else {
            http_response_code(404);
            return json_encode(["error" => "Evento no encontrado"]);
        }
    }

    /**
     * Crea un nuevo evento
     */
    public function crear($datos) {
        if (!isset($datos['titulo']) || empty($datos['titulo']) || 
            !isset($datos['fecha_hora']) || empty($datos['fecha_hora'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos título y fecha_hora son requeridos"]);
        }

        $id = $this->modelo->crear($datos);
        if ($id) {
            http_response_code(201);
            return json_encode([
                "mensaje" => "Evento creado exitosamente",
                "id_evento" => $id
            ]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo crear el evento"]);
        }
    }

    /**
     * Actualiza un evento
     */
    public function actualizar($id, $datos) {
        if (!isset($datos['titulo']) || empty($datos['titulo']) || 
            !isset($datos['fecha_hora']) || empty($datos['fecha_hora'])) {
            http_response_code(400);
            return json_encode(["error" => "Los campos título y fecha_hora son requeridos"]);
        }

        if ($this->modelo->actualizar($id, $datos)) {
            return json_encode(["mensaje" => "Evento actualizado exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo actualizar el evento"]);
        }
    }

    /**
     * Elimina un evento
     */
    public function eliminar($id) {
        if ($this->modelo->eliminar($id)) {
            return json_encode(["mensaje" => "Evento eliminado exitosamente"]);
        } else {
            http_response_code(500);
            return json_encode(["error" => "No se pudo eliminar el evento"]);
        }
    }
}
