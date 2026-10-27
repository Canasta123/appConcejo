<?php
/**
 * Modelo de Documento
 */
class Documento {
    private $conn;
    private $table = 'docpubli'; // Tabla del sitio web existente

    /**
     * Constructor con inyección de conexión
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los documentos
     */
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY 1 DESC LIMIT 100";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'normalizarCampos'], $rows);
    }

    /**
     * Cache de nombres de columna
     */
    private $cachedColumns = null;

    /**
     * Obtiene los nombres de columnas reales de la tabla
     */
    private function getColumnNames() {
        if ($this->cachedColumns === null) {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM " . $this->table);
            $stmt->execute();
            $this->cachedColumns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
        }
        return $this->cachedColumns;
    }

    private $cachedMapping = null;

    /**
     * Obtiene el mapeo inverso: campo estándar -> columna real de la tabla
     */
    private function getColumnMapping() {
        if ($this->cachedMapping !== null) {
            return $this->cachedMapping;
        }
        
        $columns = $this->getColumnNames();
        $columnsLower = array_map('strtolower', $columns);
        // Create a lookup: lowercase -> original case
        $lookup = array_combine($columnsLower, $columns);
        
        $mapping = [];
        
        // ID
        foreach (['id_doc', 'iddoc', 'id', 'cod_doc'] as $c) {
            if (isset($lookup[$c])) { $mapping['id'] = $lookup[$c]; break; }
        }
        
        // NOMBRE
        foreach (['nombre', 'titulo', 'documento', 'doc_nombre'] as $c) {
            if (isset($lookup[$c])) { $mapping['nombre'] = $lookup[$c]; break; }
        }
        
        // CATEGORIA
        foreach (['categoria', 'tipo', 'tpublicacion', 'id_tipo', 'cat'] as $c) {
            if (isset($lookup[$c])) { $mapping['categoria'] = $lookup[$c]; break; }
        }
        
        // URL PDF
        foreach (['url_pdf', 'archivo', 'pdf', 'link', 'ruta', 'doc_archivo'] as $c) {
            if (isset($lookup[$c])) { $mapping['url_pdf'] = $lookup[$c]; break; }
        }
        
        // FECHA
        foreach (['fecha_sub', 'fecha', 'fecha_carga', 'date'] as $c) {
            if (isset($lookup[$c])) { $mapping['fecha_sub'] = $lookup[$c]; break; }
        }
        
        $this->cachedMapping = $mapping;
        return $mapping;
    }

    /**
     * Obtiene documentos filtrados por categoría
     */
    public function obtenerPorCategoria($categoria) {
        $map = $this->getColumnMapping();
        $colCategoria = $map['categoria'] ?? 'categoria';

        $query = "SELECT * FROM " . $this->table . " WHERE `$colCategoria` = :categoria ORDER BY 1 DESC LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'normalizarCampos'], $rows);
    }

    /**
     * Obtiene un documento por su ID
     */
    public function obtenerPorId($id) {
        $map = $this->getColumnMapping();
        $colId = $map['id'] ?? 'id_doc';

        $query = "SELECT * FROM " . $this->table . " WHERE `$colId` = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizarCampos($row) : false;
    }

    /**
     * Normaliza campos de docpubli para la app
     */
    private function normalizarCampos($row) {
        return [
            'id_doc' => $row['id_doc'] ?? $row['id'] ?? $row['iddoc'] ?? 0,
            'nombre' => $row['nombre'] ?? $row['titulo'] ?? $row['documento'] ?? '',
            'categoria' => $row['categoria'] ?? $row['tipo'] ?? $row['tpublicacion'] ?? 'Acuerdo',
            'url_pdf' => $row['url_pdf'] ?? $row['archivo'] ?? $row['pdf'] ?? $row['link'] ?? '',
            'fecha_sub' => $row['fecha_sub'] ?? $row['fecha'] ?? date('Y-m-d')
        ];
    }

    /**
     * Crea un nuevo documento
     */
    public function crear($datos) {
        $map = $this->getColumnMapping();
        $colNombre = $map['nombre'] ?? 'nombre';
        $colCategoria = $map['categoria'] ?? 'categoria';
        $colUrlPdf = $map['url_pdf'] ?? 'url_pdf';
        $colFechaSub = $map['fecha_sub'] ?? 'fecha_sub';

        $query = "INSERT INTO " . $this->table . " 
                  (`$colNombre`, `$colCategoria`, `$colUrlPdf`, `$colFechaSub`) 
                  VALUES (:nombre, :categoria, :url_pdf, :fecha_sub)";
        
        $stmt = $this->conn->prepare($query);
        
        $fecha_sub = isset($datos['fecha_sub']) ? $datos['fecha_sub'] : date('Y-m-d');
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':url_pdf', $datos['url_pdf']);
        $stmt->bindParam(':fecha_sub', $fecha_sub);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualiza un documento existente
     */
    public function actualizar($id, $datos) {
        $map = $this->getColumnMapping();
        $colNombre = $map['nombre'] ?? 'nombre';
        $colCategoria = $map['categoria'] ?? 'categoria';
        $colUrlPdf = $map['url_pdf'] ?? 'url_pdf';
        $colId = $map['id'] ?? 'id_doc';

        $query = "UPDATE " . $this->table . " 
                  SET `$colNombre` = :nombre, `$colCategoria` = :categoria, `$colUrlPdf` = :url_pdf 
                  WHERE `$colId` = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':url_pdf', $datos['url_pdf']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Elimina un documento por su ID
     */
    public function eliminar($id) {
        $map = $this->getColumnMapping();
        $colId = $map['id'] ?? 'id_doc';

        $query = "DELETE FROM " . $this->table . " WHERE `$colId` = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
