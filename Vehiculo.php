<?php
class Vehiculo {
    private $id;
    private $cliente_id;
    private $marca;
    private $modelo;

    public function __construct($cliente_id, $marca, $modelo, $id = null) {
        $this->id = $id;
        $this->cliente_id = $cliente_id;
        $this->marca = $marca;
        $this->modelo = $modelo;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getClienteId() { return $this->cliente_id; }
    public function setClienteId($cliente_id) { $this->cliente_id = $cliente_id; }

    public function getMarca() { return $this->marca; }
    public function setMarca($marca) { $this->marca = $marca; }

    public function getModelo() { return $this->modelo; }
    public function setModelo($modelo) { $this->modelo = $modelo; }

    public function insert() {
        try {
            $conexion = Conexion::getInstance()->getConnection();
            
            $sql = "INSERT INTO vehiculos (cliente_id, marca, modelo) VALUES (:cliente_id, :marca, :modelo)";
            $stmt = $conexion->prepare($sql);
            
            $stmt->bindParam(':cliente_id', $this->cliente_id);
            $stmt->bindParam(':marca', $this->marca);
            $stmt->bindParam(':modelo', $this->modelo);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            die("Error al insertar vehículo: " . $e->getMessage());
        }
    }

    public static function read($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM vehiculos WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al leer vehículo: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE vehiculos SET cliente_id = :cliente_id, marca = :marca, modelo = :modelo WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':cliente_id', $this->cliente_id);
            $stmt->bindParam(':marca', $this->marca);
            $stmt->bindParam(':modelo', $this->modelo);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar vehículo: " . $e->getMessage());
        }
    }

    public static function delete($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM vehiculos WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al borrar vehículo: " . $e->getMessage());
        }
    }
}
?>