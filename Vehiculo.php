<?php
class Vehiculo {
    private $matricula;
    private $cliente_dni;
    private $marca;
    private $modelo;
    private $anyo;

    public function __construct($matricula, $cliente_dni, $marca, $modelo, $anyo) {
        $this->matricula = $matricula;
        $this->cliente_dni = $cliente_dni;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->anyo = $anyo;
    }

    public function getMatricula() { return $this->matricula; }
    public function setMatricula($matricula) { $this->matricula = $matricula; }

    public function getClienteDni() { return $this->cliente_dni; }
    public function setClienteDni($cliente_dni) { $this->cliente_dni = $cliente_dni; }

    public function getMarca() { return $this->marca; }
    public function setMarca($marca) { $this->marca = $marca; }

    public function getModelo() { return $this->modelo; }
    public function setModelo($modelo) { $this->modelo = $modelo; }

    public function getAnyo() { return $this->anyo; }
    public function setAnyo($anyo) { $this->anyo = $anyo; }

    
    // Respuesta estructurada de error en JSON
    private static function sendJsonError($mensaje, $code = 500) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            "status" => "error",
            "message" => $mensaje
        ]);
        exit;
    }

    public function insert() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "INSERT INTO vehiculos (matricula, cliente_dni, marca, modelo, anyo) 
                    VALUES (:matricula, :cliente_dni, :marca, :modelo, :anyo)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':matricula', $this->matricula);
            $stmt->bindParam(':cliente_dni', $this->cliente_dni);
            $stmt->bindParam(':marca', $this->marca);
            $stmt->bindParam(':modelo', $this->modelo);
            $stmt->bindParam(':anyo', $this->anyo);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al insertar vehículo: " . $e->getMessage());
        }
    }

    public static function read($matricula) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM vehiculos WHERE matricula = :matricula";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':matricula', $matricula);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                // Retornamos una nueva instancia con los datos mapeados
                return new Vehiculo(
                    $data['matricula'],
                    $data['cliente_dni'],
                    $data['marca'],
                    $data['modelo'],
                    $data['anyo']
                );
            }
            return null;
        } catch (PDOException $e) {
            self::sendJsonError("Error al leer vehículo: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE vehiculos 
                    SET cliente_dni = :cliente_dni, marca = :marca, modelo = :modelo, anyo = :anyo 
                    WHERE matricula = :matricula";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':cliente_dni', $this->cliente_dni);
            $stmt->bindParam(':marca', $this->marca);
            $stmt->bindParam(':modelo', $this->modelo);
            $stmt->bindParam(':anyo', $this->anyo);
            $stmt->bindParam(':matricula', $this->matricula);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al actualizar vehículo: " . $e->getMessage());
        }
    }

    public function delete() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM vehiculos WHERE matricula = :matricula";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':matricula', $this->matricula);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al borrar vehículo: " . $e->getMessage());
        }
    }

    public function getCitas() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM citas WHERE vehiculo_matricula = :vehiculo_matricula";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':vehiculo_matricula', $this->matricula);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            self::sendJsonError("Error al consultar citas del vehículo: " . $e->getMessage());
        }
    }
}
?>