<?php
class Cliente {
    private $id;
    private $dni;
    private $nombre;
    private $telefono;

    public function __construct($dni, $nombre, $telefono, $id = null) {
        $this->id = $id;
        $this->dni = $dni;
        $this->nombre = $nombre;
        $this->telefono = $telefono;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getDni() { return $this->dni; }
    public function setDni($dni) { $this->dni = $dni; }

    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }

    public function getTelefono() { return $this->telefono; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }

    public function insert() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "INSERT INTO clientes (dni, nombre, telefono) VALUES (:dni, :nombre, :telefono)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':dni', $this->dni);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':telefono', $this->telefono);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al insertar cliente: " . $e->getMessage());
        }
    }

    public static function read($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();
        
            $sql = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al leer cliente: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE clientes SET dni = :dni, nombre = :nombre, telefono = :telefono WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':dni', $this->dni);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    public static function delete($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al borrar cliente: " . $e->getMessage());
        }
    }
}
?>