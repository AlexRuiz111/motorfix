<?php
class Usuario {
    private $id;
    private $email;
    private $password;
    private $nombre;
    private $rol;

    public function __construct($email, $password, $nombre, $rol, $id = null) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->rol = $rol;
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id;}

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }

    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }

    public function getRol() { return $this->rol; }
    public function setRol($rol) { $this->rol = $rol; }

    public function insert() {
        try {
            $conexion = Conexion::getInstance()->getConnection();
            $passwordCifrada = password_hash($this->password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (email, password, nombre, rol) VALUES (:email, :password, :nombre, :rol)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $passwordCifrada);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':rol', $this->rol);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al insertar usuario: " . $e->getMessage());
        } 
    }

    public static function read($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM usuarios WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (PDOException $e) {
            die("Error al leer usuario: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE usuarios SET email = :email, nombre = :nombre, rol = :rol WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':rol', $this->rol);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    public static function delete($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM usuarios WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al borrar usuario: " . $e->getMessage());
        }
    }
}
?>