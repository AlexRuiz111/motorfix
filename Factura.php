<?php
class Factura {
    private $id;
    private $cita_id;
    private $fecha_emision;
    private $subtotal;
    private $iva;
    private $total;
    private $estado_pago;

    public function __construct($cita_id, $fecha_emision, $subtotal, $iva = 21.0, $estado_pago = 'Pendiente', $id = null) {
        $this->id = $id;
        $this->cita_id = $cita_id;
        $this->fecha_emision = $fecha_emision;
        $this->subtotal = $subtotal;
        $this->iva = $iva;
        $this->estado_pago = $estado_pago;
        $this->total = $this->calcularTotal();
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getCitaId() { return $this->cita_id; }
    public function setCitaId($cita_id) { $this->cita_id = $cita_id; }

    public function getFechaEmision() { return $this->fecha_emision; }
    public function setFechaEmision($fecha_emision) { $this->fecha_emision = $fecha_emision; }

    public function getSubtotal() { return $this->subtotal; }
    public function setSubtotal($subtotal) { 
        $this->subtotal = $subtotal; 
        $this->total = $this->calcularTotal();
    }

    public function getIva() { return $this->iva; }
    public function setIva($iva) { 
        $this->iva = $iva; 
        $this->total = $this->calcularTotal();
    }

    public function getTotal() { return $this->total; }

    public function getEstadoPago() { return $this->estado_pago; }
    public function setEstadoPago($estado_pago) { $this->estado_pago = $estado_pago; }

    public function insert() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "INSERT INTO facturas (cita_id, fecha_emision, subtotal, iva, total, estado_pago) 
                    VALUES (:cita_id, :fecha_emision, :subtotal, :iva, :total, :estado_pago)";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':cita_id', $this->cita_id);
            $stmt->bindParam(':fecha_emision', $this->fecha_emision);
            $stmt->bindParam(':subtotal', $this->subtotal);
            $stmt->bindParam(':iva', $this->iva);
            $stmt->bindParam(':total', $this->total);
            $stmt->bindParam(':estado_pago', $this->estado_pago);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al insertar factura: " . $e->getMessage());
        }
    }

    public static function read($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "SELECT * FROM facturas WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al leer factura: " . $e->getMessage());
        }
    }

    public function update() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE facturas 
                    SET cita_id = :cita_id, fecha_emision = :fecha_emision, subtotal = :subtotal, iva = :iva, total = :total, estado_pago = :estado_pago 
                    WHERE id = :id";
            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(':cita_id', $this->cita_id);
            $stmt->bindParam(':fecha_emision', $this->fecha_emision);
            $stmt->bindParam(':subtotal', $this->subtotal);
            $stmt->bindParam(':iva', $this->iva);
            $stmt->bindParam(':total', $this->total);
            $stmt->bindParam(':estado_pago', $this->estado_pago);
            $stmt->bindParam(':id', $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar factura: " . $e->getMessage());
        }
    }

    public static function delete($id) {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "DELETE FROM facturas WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al borrar factura: " . $e->getMessage());
        }
    }

    public function calcularTotal() {
        return round($this->subtotal + ($this->subtotal * ($this->iva / 100)), 2);
    }

    public function marcarComoPagada() {
        try {
            $conexion = Conexion::getInstance()->getConnection();

            $sql = "UPDATE facturas SET estado_pago = 'Pagada' WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $this->id);

            if ($stmt->execute()) {
                $this->estado_pago = 'Pagada';
                return true;
            }
            return false;
        } catch (PDOException $e) {
            die("Error al marcar la factura como pagada: " . $e->getMessage());
        }
    }
}
?>