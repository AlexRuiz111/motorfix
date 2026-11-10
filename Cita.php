<?php
class Cita {
    private $id;
    private $vehiculo_matricula;
    private $fecha_hora;
    private $motivo;
    private $estado;

    public function __construct($vehiculo_matricula, $fecha_hora, $motivo, $estado = 'Pendiente', $id = null) {
        $this->id = $id;
        $this->vehiculo_matricula = $vehiculo_matricula;
        $this->fecha_hora = $fecha_hora;
        $this->motivo = $motivo;
        $this->estado = $estado;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getVehiculoMatricula() { return $this->vehiculo_matricula; }
    public function setVehiculoMatricula($vehiculo_matricula) { $this->vehiculo_matricula = $vehiculo_matricula; }

    public function getFechaHora() { return $this->fecha_hora; }
    public function setFechaHora($fecha_hora) { $this->fecha_hora = $fecha_hora; }

    public function getMotivo() { return $this->motivo; }
    public function setMotivo($motivo) { $this->motivo = $motivo; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

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

            $sql = "INSERT INTO citas (vehiculo_matricula, fecha_hora, motivo, estado) 
                    VALUES (:vehiculo_matricula, :fecha_hora, :motivo, :estado)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':vehiculo_matricula', $this->vehiculo_matricula);
            $stmt->bindParam(':fecha_hora', $this->fecha_hora);
            $stmt->bindParam(':motivo', $this->motivo);
            $stmt->bindParam(':estado', $this->estado);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al insertar cita: " . $e->getMessage());
        }
    }

    public static function read($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM citas WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                return new Cita(
                    $data['vehiculo_matricula'],
                    $data['fecha_hora'],
                    $data['motivo'],
                    $data['estado'],
                    $data['id']
                );
            }
            return null;
        } catch (PDOException $e) {
            self::sendJsonError("Error al leer cita: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE citas 
                    SET vehiculo_matricula = :vehiculo_matricula, fecha_hora = :fecha_hora, motivo = :motivo, estado = :estado 
                    WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':vehiculo_matricula', $this->vehiculo_matricula);
            $stmt->bindParam(':fecha_hora', $this->fecha_hora);
            $stmt->bindParam(':motivo', $this->motivo);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al actualizar cita: " . $e->getMessage());
        }
    }

    public function delete() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM citas WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al borrar cita: " . $e->getMessage());
        }
    }

    public function cambiarEstado($nuevoEstado) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE citas SET estado = :estado WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $this->id);

            if ($stmt->execute()) {
                $this->estado = $nuevoEstado;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            self::sendJsonError("Error al cambiar estado de la cita: " . $e->getMessage());
        }
    }
}
?>