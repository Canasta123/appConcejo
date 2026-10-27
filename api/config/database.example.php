<?php
/**
 * Configuración de la base de datos (Plantilla de ejemplo)
 * Renombrar a database.php y configurar con las credenciales correspondientes.
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'tu_base_de_datos';
    private $username = 'tu_usuario';
    private $password = 'tu_password';
    private $conn;

    /**
     * Obtiene la conexión PDO a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $exception) {
            echo json_encode(["error" => "Error de conexión: " . $exception->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}
