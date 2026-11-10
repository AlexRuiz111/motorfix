<?php
class Cliente {
    private $dni;
    private $nombre;
    private $apellidos;
    private $telefono;
    private $email;
    private $password;

    public function __construct($dni, $nombre, $apellidos, $telefono, $email, $password) {
        $this->dni = $dni;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->telefono = $telefono;
        $this->email = $email;
        $this->password = $password;
    }

    public function getDni() { return $this->dni; }
    public function setDni($dni) { $this->dni = $dni; }

    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }

    public function getApellidos() { return $this->apellidos; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }

    public function getTelefono() { return $this->telefono; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }
    
    // Función centralizada para emitir respuestas de error en JSON
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

            $sql = "INSERT INTO clientes (dni, nombre, apellidos, telefono, email, password) 
                    VALUES (:dni, :nombre, :apellidos, :telefono, :email, :password)";
            
            $stmt = $conexion->prepare($sql);
            $hashPassword = password_hash($this->password, PASSWORD_BCRYPT);

            $stmt->bindParam(':dni', $this->dni);
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellidos', $this->apellidos);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $hashPassword);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al insertar cliente: " . $e->getMessage());
        }
    }

    public static function read($dni) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM clientes WHERE dni = :dni";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':dni', $dni);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                return new Cliente(
                    $data['dni'],
                    $data['nombre'],
                    $data['apellidos'],
                    $data['telefono'],
                    $data['email'],
                    $data['password']
                );
            }
            return null; 
        } catch (PDOException $e) {
            self::sendJsonError("Error al leer cliente: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE clientes 
                    SET nombre = :nombre, apellidos = :apellidos, telefono = :telefono, email = :email 
                    WHERE dni = :dni";
            
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':apellidos', $this->apellidos);
            $stmt->bindParam(':telefono', $this->telefono);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':dni', $this->dni);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al actualizar cliente: " . $e->getMessage());
        }
    }

    public function delete() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM clientes WHERE dni = :dni";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':dni', $this->dni);

            return $stmt->execute();
        } catch (PDOException $e) {
            self::sendJsonError("Error al borrar cliente: " . $e->getMessage());
        }
    }

    // Autenticación: Devuelve la instancia del Cliente autenticado
    public static function login($email, $password) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM clientes WHERE email = :email";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data && password_verify($password, $data['password'])) {
                // Retorna objeto Cliente autenticado
                return new Cliente(
                    $data['dni'],
                    $data['nombre'],
                    $data['apellidos'],
                    $data['telefono'],
                    $data['email'],
                    $data['password']
                );
            }
            return false;
        } catch (PDOException $e) {
            self::sendJsonError("Error en la autenticación: " . $e->getMessage());
        }
    }

    // Devuelve lista de vehículos vinculados a la instancia del cliente
    public function getVehiculos() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM vehiculos WHERE cliente_dni = :cliente_dni";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':cliente_dni', $this->dni);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            self::sendJsonError("Error al consultar vehículos del cliente: " . $e->getMessage());
        }
    }
}
?>