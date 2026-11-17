<?php
// Inicia la sesión para poder identificar qué cliente hace la petición
session_start();

// Indica al navegador que toda respuesta de este archivo será JSON puro
header('Content-Type: application/json');

// Importa las clases del modelo y la conexión Singleton
require_once 'Conexion.php';
require_once 'Cliente.php';
require_once 'Vehiculo.php';
require_once 'Cita.php';
require_once 'Factura.php';

// BARRERA DE SEGURIDAD: Comprueba si hay un cliente autenticado en la sesión
if (!isset($_SESSION['cliente_dni'])) {
    http_response_code(401); 
    echo json_encode([
        "status" => "error",
        "message" => "Acceso denegado. Se requiere iniciar sesión."
    ]);
    exit;
}

// Guarda el DNI del cliente logueado en una variable
$dni_cliente = $_SESSION['cliente_dni'];

// CAPTURA DE DATOS: Lee si llega información por URL (GET) o por cuerpo JSON (POST)
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($input['action'] ?? '');

// ENRUTADOR DE ACCIONES (Switch principal del panel de cliente)
switch ($action) {

    // --- BLOQUE VEHÍCULOS ---
    case 'get_vehiculos':
        obtenerVehiculos($dni_cliente);
        break;

    case 'add_vehiculo':
        agregarVehiculo($dni_cliente, $input);
        break;

    // --- BLOQUE CITAS ---
    case 'get_citas':
        obtenerCitas($dni_cliente);
        break;

    case 'add_cita':
        agregarCita($dni_cliente, $input);
        break;

    // --- BLOQUE FACTURAS ---
    case 'get_facturas':
        obtenerFacturas($dni_cliente);
        break;

    default:
        http_response_code(400); 
        echo json_encode([
            "status" => "error",
            "message" => "Acción no reconocida."
        ]);
        break;
}

// =========================================================================
// FUNCIONES CONTROLADORAS
// =========================================================================

function obtenerVehiculos($dni) {
    $cliente = Cliente::read($dni);

    if ($cliente) {
        $vehiculos = $cliente->getVehiculos();
        
        echo json_encode([
            "status" => "success",
            "data" => $vehiculos
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "No se encontró el perfil del cliente."
        ]);
    }
}

function agregarVehiculo($dni, $data) {
    if (empty($data['matricula']) || empty($data['marca']) || empty($data['modelo']) || empty($data['anyo'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Faltan datos obligatorios del vehículo."
        ]);
        exit;
    }

    $matricula = strtoupper(trim($data['matricula']));
    $marca = trim($data['marca']);
    $modelo = trim($data['modelo']);
    $anyo = intval($data['anyo']);

    if (Vehiculo::read($matricula) !== null) {
        http_response_code(409); 
        echo json_encode([
            "status" => "error",
            "message" => "La matrícula ya está registrada."
        ]);
        exit;
    }

    $nuevoVehiculo = new Vehiculo($matricula, $dni, $marca, $modelo, $anyo);

    if ($nuevoVehiculo->insert()) {
        http_response_code(201); 
        echo json_encode([
            "status" => "success",
            "message" => "Vehículo añadido correctamente."
        ]);
    }
}

function obtenerCitas($dni) {
    $cliente = Cliente::read($dni);
    
    if (!$cliente) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Cliente no encontrado."]);
        return;
    }

    $vehiculos = $cliente->getVehiculos();
    $todasLasCitas = [];

    // Recorre los vehículos del cliente y obtiene las citas de cada uno
    foreach ($vehiculos as $vehiculoData) {
        $vehiculoObj = new Vehiculo(
            $vehiculoData['matricula'], 
            $vehiculoData['cliente_dni'], 
            $vehiculoData['marca'], 
            $vehiculoData['modelo'], 
            $vehiculoData['anyo']
        );
        $citasVehiculo = $vehiculoObj->getCitas();
        if ($citasVehiculo) {
            $todasLasCitas = array_merge($todasLasCitas, $citasVehiculo);
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $todasLasCitas
    ]);
}

function agregarCita($dni, $data) {
    if (empty($data['vehiculo_matricula']) || empty($data['fecha_hora']) || empty($data['motivo'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Faltan datos para la cita."]);
        exit;
    }

    // Verifica que la matrícula pertenezca realmente a este cliente por seguridad
    $matricula = strtoupper(trim($data['vehiculo_matricula']));
    $vehiculo = Vehiculo::read($matricula);
    
    if (!$vehiculo || $vehiculo->getClienteDni() !== $dni) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "El vehículo no pertenece al usuario actual."]);
        exit;
    }

    $nuevaCita = new Cita($matricula, $data['fecha_hora'], $data['motivo']);

    if ($nuevaCita->insert()) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Cita solicitada correctamente."]);
    }
}

function obtenerFacturas($dni) {
    try {
        $conexion = Conexion::getInstance()->getConnection();
        
        // Mediante JOIN, busca las facturas unidas a las citas de los vehículos del cliente actual
        $sql = "SELECT f.*, c.vehiculo_matricula 
                FROM facturas f
                INNER JOIN citas c ON f.cita_id = c.id
                INNER JOIN vehiculos v ON c.vehiculo_matricula = v.matricula
                WHERE v.cliente_dni = :dni";
                
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':dni', $dni);
        $stmt->execute();
        
        $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "status" => "success",
            "data" => $facturas
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error al obtener facturas."]);
    }
}
?>