<?php
/**
 * Modelo de Evento (Agenda)
 */
class Evento {
    private $conn;
    private $table = 'agenda';

    /**
     * Constructor con inyección de conexión
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene los eventos de un mes y año específicos
     */
    public function obtenerPorMes($mes, $anio) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE MONTH(fecha_hora) = :mes AND YEAR(fecha_hora) = :anio 
                      ORDER BY fecha_hora ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al consultar agenda: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los próximos eventos a partir de la fecha actual
     */
    public function obtenerProximos($limite = 5) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE fecha_hora >= NOW() 
                      ORDER BY fecha_hora ASC 
                      LIMIT :limite";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al consultar próximos eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un evento por su ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_evento = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Crea un nuevo evento
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (titulo, fecha_hora, link_yt) 
                      VALUES (:titulo, :fecha_hora, :link_yt)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':titulo', $datos['titulo']);
            $stmt->bindParam(':fecha_hora', $datos['fecha_hora']);
            $stmt->bindParam(':link_yt', $datos['link_yt']);
            
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Actualiza un evento existente
     */
    public function actualizar($id, $datos) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET titulo = :titulo, fecha_hora = :fecha_hora, link_yt = :link_yt 
                      WHERE id_evento = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':titulo', $datos['titulo']);
            $stmt->bindParam(':fecha_hora', $datos['fecha_hora']);
            $stmt->bindParam(':link_yt', $datos['link_yt']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Elimina un evento por su ID
     */
    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id_evento = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
