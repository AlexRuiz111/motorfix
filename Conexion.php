<?php
class Conexion {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $envFile = __DIR__ . '/.env'; 

        if (!file_exists($envFile)) {
            $this->sendJsonError("Archivo .env no encontrado.");
        }

        $env = parse_ini_file($envFile);
        if (!$env) {
            $this->sendJsonError("Error de sintaxis al leer el archivo .env.");
        }

        $host = $env['DB_HOST'] ?? 'localhost';
        $dbName = $env['DB_NAME'] ?? 'motorfix';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? '';

        try {
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->connection = new PDO($dsn, $user, $pass, $opciones);
            
        } catch (PDOException $e) {
            $this->sendJsonError("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Conexion();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Método de soporte para estructurar la respuesta de error en JSON
    private function sendJsonError($mensaje) {
        header('Content-Type: application/json');
        http_response_code(500); // Código HTTP 500: Error interno del servidor
        
        echo json_encode([
            "status" => "error",
            "message" => $mensaje
        ]);
        exit; // Detiene la ejecución limpiamente
    }
}
?>