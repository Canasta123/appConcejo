<?php
/**
 * Modelo de Noticia - Auto-descubre columnas de la tabla noticoncejo
 */
class Noticia {
    private $conn;
    private $table = 'noticoncejo'; // Tabla del sitio web existente

    /**
     * Constructor con inyección de la conexión PDO
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las noticias activas ordenadas por fecha de publicación
     * @return array
     */
    public function obtenerActivas() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY 1 DESC LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normaliza campos para que el frontend reciba formato estándar
        return array_map([$this, 'normalizarCampos'], $rows);
    }

    /**
     * Obtiene una noticia por su ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        // Busca por cualquier columna que pueda ser el ID
        $columns = $this->getColumnNames();
        $idCol = null;
        foreach (['id_noticia', 'id', 'idnoticia', 'ID'] as $candidate) {
            if (in_array($candidate, $columns)) {
                $idCol = $candidate;
                break;
            }
        }
        if (!$idCol) $idCol = $columns[0]; // Usar la primera columna como fallback

        $query = "SELECT * FROM " . $this->table . " WHERE `$idCol` = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->normalizarCampos($row) : false;
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

    /**
     * Normaliza los nombres de columnas de la BD legacy para el frontend.
     * Busca en múltiples posibles nombres de columna para cada campo estandarizado.
     */
    private function normalizarCampos($row) {
        // Estrategia: buscar cada campo en múltiples posibles nombres de columna
        // La clave es que revisamos TODAS las columnas disponibles
        $keys = array_keys($row);
        $keysLower = array_map('strtolower', $keys);
        $rowLower = [];
        foreach ($row as $k => $v) {
            $rowLower[strtolower($k)] = $v;
        }

        // ID: buscar columnas que contengan 'id'
        $id = 0;
        foreach (['id_noticia', 'idnoticia', 'id', 'noti_id', 'noticiaid', 'id_noti', 'cod_noticia'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c]) { $id = $rowLower[$c]; break; }
        }

        // TÍTULO: buscar columnas que contengan 'titul', 'nombre', 'subject', 'header', 'asunto'
        $titulo = '';
        foreach (['titulo', 'titular', 'nombre', 'title', 'asunto', 'subject', 'noti_titulo', 'encabezado', 'noti_tit'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c] !== '') { $titulo = $rowLower[$c]; break; }
        }
        if ($titulo === '') {
            foreach ($keysLower as $idx => $k) {
                if ((strpos($k, 'titul') !== false || strpos($k, 'nombre') !== false || strpos($k, 'asunto') !== false) && isset($row[$keys[$idx]]) && $row[$keys[$idx]] !== '') {
                    $titulo = $row[$keys[$idx]];
                    break;
                }
            }
        }

        // CONTENIDO: buscar columnas de texto
        $contenido = '';
        foreach (['contenido', 'descripcion', 'texto', 'cuerpo', 'detalle', 'content', 'body', 'noti_contenido', 'noti_detalle', 'noti_descripcion', 'html', 'resumen', 'noti_resumen', 'nota', 'fulltext', 'post_content', 'noti_texto'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c] !== '') { $contenido = $rowLower[$c]; break; }
        }
        if ($contenido === '') {
            foreach ($keysLower as $idx => $k) {
                if ((strpos($k, 'conteni') !== false || strpos($k, 'descrip') !== false || strpos($k, 'texto') !== false || strpos($k, 'detalle') !== false || strpos($k, 'cuerpo') !== false || strpos($k, 'resumen') !== false || strpos($k, 'nota') !== false) && isset($row[$keys[$idx]]) && $row[$keys[$idx]] !== '') {
                    $contenido = $row[$keys[$idx]];
                    break;
                }
            }
        }
        // Fallback final para contenido: cualquier columna de texto largo que no sea el título
        if ($contenido === '') {
            foreach ($row as $k => $v) {
                if (is_string($v) && strlen($v) > 25 && $v !== $titulo) {
                    $contenido = $v;
                    break;
                }
            }
        }

        // IMAGEN: buscar columnas de imagen
        $url_imagen = null;
        foreach (['url_imagen', 'imagen', 'img', 'foto', 'image', 'url_img', 'noti_imagen', 'portada', 'poster', 'afiche', 'banner', 'media', 'archivo_imagen', 'ruta_imagen', 'ruta', 'noti_img', 'pic'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c] !== '' && $rowLower[$c] !== null) { $url_imagen = $rowLower[$c]; break; }
        }
        if ($url_imagen === null) {
            foreach ($keysLower as $idx => $k) {
                $val = $row[$keys[$idx]];
                if ($val === null || $val === '') continue;
                if (is_string($val) && (
                    strpos($k, 'imagen') !== false || strpos($k, 'img') !== false || 
                    strpos($k, 'foto') !== false || strpos($k, 'image') !== false ||
                    strpos($k, 'poster') !== false || strpos($k, 'media') !== false ||
                    strpos($k, 'archivo') !== false || strpos($k, 'ruta') !== false
                )) {
                    $url_imagen = $val;
                    break;
                }
                if (is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $val)) {
                    $url_imagen = $val;
                    break;
                }
            }
        }

        // Si aún es nulo pero hay contenido HTML o texto, buscar etiquetas <img> o URLs de imagen
        if (empty($url_imagen) && !empty($contenido)) {
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $contenido, $matches)) {
                $url_imagen = $matches[1];
            } elseif (preg_match('/(https?:\/\/[^\s"\']+\.(?:jpg|jpeg|png|gif|webp))/i', $contenido, $matches)) {
                $url_imagen = $matches[1];
            }
        }

        // FECHA
        $fecha_pub = date('Y-m-d H:i:s');
        foreach (['fecha_pub', 'fecha', 'fechacreacion', 'fecha_creacion', 'fecha_publicacion', 'date', 'created_at', 'noti_fecha', 'fech_publi', 'fec_pub'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c] !== '' && $rowLower[$c] !== null) { $fecha_pub = $rowLower[$c]; break; }
        }
        if ($fecha_pub === date('Y-m-d H:i:s')) {
            foreach ($keysLower as $idx => $k) {
                if ((strpos($k, 'fecha') !== false || strpos($k, 'date') !== false) && isset($row[$keys[$idx]]) && $row[$keys[$idx]] !== '') {
                    $fecha_pub = $row[$keys[$idx]];
                    break;
                }
            }
        }

        // ESTADO
        $estado = 'activo';
        foreach (['estado', 'status', 'activo', 'active', 'noti_estado'] as $c) {
            if (isset($rowLower[$c]) && $rowLower[$c] !== '') { $estado = $rowLower[$c]; break; }
        }

        return [
            'id_noticia' => $id,
            'titulo' => $titulo,
            'contenido' => $contenido,
            'url_imagen' => $url_imagen,
            'fecha_pub' => $fecha_pub,
            'estado' => $estado,
            // Include ALL raw columns for debugging (frontend can use them)
            '_raw_columns' => array_keys($row)
        ];
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
        foreach (['id_noticia', 'idnoticia', 'id', 'noti_id', 'noticiaid', 'id_noti', 'cod_noticia'] as $c) {
            if (isset($lookup[$c])) { $mapping['id'] = $lookup[$c]; break; }
        }
        
        // TÍTULO
        foreach (['titulo', 'titular', 'nombre', 'title', 'asunto', 'subject', 'noti_titulo', 'encabezado', 'noti_tit'] as $c) {
            if (isset($lookup[$c])) { $mapping['titulo'] = $lookup[$c]; break; }
        }
        
        // CONTENIDO
        foreach (['contenido', 'descripcion', 'texto', 'cuerpo', 'detalle', 'content', 'body', 'noti_contenido', 'noti_detalle', 'noti_descripcion', 'html', 'resumen', 'noti_resumen', 'nota', 'fulltext', 'post_content', 'noti_texto'] as $c) {
            if (isset($lookup[$c])) { $mapping['contenido'] = $lookup[$c]; break; }
        }
        
        // IMAGEN
        foreach (['url_imagen', 'imagen', 'img', 'foto', 'image', 'url_img', 'noti_imagen', 'portada', 'poster', 'afiche', 'banner', 'media', 'archivo_imagen', 'ruta_imagen', 'ruta', 'noti_img', 'pic'] as $c) {
            if (isset($lookup[$c])) { $mapping['url_imagen'] = $lookup[$c]; break; }
        }
        
        // ESTADO
        foreach (['estado', 'status', 'activo', 'active', 'noti_estado'] as $c) {
            if (isset($lookup[$c])) { $mapping['estado'] = $lookup[$c]; break; }
        }
        
        // FECHA
        foreach (['fecha_pub', 'fecha', 'fechacreacion', 'fecha_creacion', 'fecha_publicacion', 'date', 'created_at', 'noti_fecha', 'fech_publi', 'fec_pub'] as $c) {
            if (isset($lookup[$c])) { $mapping['fecha_pub'] = $lookup[$c]; break; }
        }
        
        $this->cachedMapping = $mapping;
        return $mapping;
    }


    /**
     * Crea una nueva noticia
     * @param array $datos
     * @return int|false ID insertado o false
     */
    public function crear($datos) {
        $map = $this->getColumnMapping();
        $colTitulo = $map['titulo'] ?? 'titulo';
        $colContenido = $map['contenido'] ?? 'contenido';
        $colImagen = $map['url_imagen'] ?? 'url_imagen';
        $colEstado = $map['estado'] ?? 'estado';
        $colFecha = $map['fecha_pub'] ?? 'fecha_pub';

        $query = "INSERT INTO " . $this->table . " 
                  (`$colTitulo`, `$colContenido`, `$colImagen`, `$colEstado`, `$colFecha`) 
                  VALUES (:titulo, :contenido, :url_imagen, :estado, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanear y vincular
        $estado = isset($datos['estado']) ? $datos['estado'] : 'activo';
        
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':contenido', $datos['contenido']);
        $stmt->bindParam(':url_imagen', $datos['url_imagen']);
        $stmt->bindParam(':estado', $estado);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualiza una noticia existente
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar($id, $datos) {
        $map = $this->getColumnMapping();
        $colTitulo = $map['titulo'] ?? 'titulo';
        $colContenido = $map['contenido'] ?? 'contenido';
        $colImagen = $map['url_imagen'] ?? 'url_imagen';
        $colEstado = $map['estado'] ?? 'estado';
        $colId = $map['id'] ?? 'id_noticia';

        $query = "UPDATE " . $this->table . " 
                  SET `$colTitulo` = :titulo, `$colContenido` = :contenido, `$colImagen` = :url_imagen, `$colEstado` = :estado 
                  WHERE `$colId` = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':titulo', $datos['titulo']);
        $stmt->bindParam(':contenido', $datos['contenido']);
        $stmt->bindParam(':url_imagen', $datos['url_imagen']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Cambia el estado de una noticia (ej: activo/inactivo)
     * @param int $id
     * @param string $estado
     * @return bool
     */
    public function cambiarEstado($id, $estado) {
        $map = $this->getColumnMapping();
        $colEstado = $map['estado'] ?? 'estado';
        $colId = $map['id'] ?? 'id_noticia';

        $query = "UPDATE " . $this->table . " SET `$colEstado` = :estado WHERE `$colId` = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Elimina una noticia por su ID
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        $map = $this->getColumnMapping();
        $colId = $map['id'] ?? 'id_noticia';

        $query = "DELETE FROM " . $this->table . " WHERE `$colId` = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
