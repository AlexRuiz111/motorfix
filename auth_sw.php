<?php
// Configuración de cabeceras para API JSON
header('Content-Type: application/json'); // Php otorga un objeto de texto en formato JSON.
header('Access-Control-Allow-Origin: *'); // Permite que las peticiones vengan desde cualquier dominio. Evita bloqueos de seguridad del navegador
header('Access-Control-Allow-Methods: POST'); // Limita este archivo a recibir únicamente peticiones de tipo POST.
header('Access-Control-Allow-Headers: Content-Type'); // Autoriza al frontend a enviar la cabecera Content-Type en su petición fetch.

session_start();

// Pega el codigo de los siguientes archivos
require_once 'Conexion.php';
require_once 'Cliente.php';

$inputJSON = file_get_contents('php://input'); // Lee la información en texto plano (el JSON crudo) que llega en el cuerpo de la petición HTTP.
$data = json_decode($inputJSON, true); // Traduce ese texto plano JSON a un array de PHP para poder acceder a los datos.

// Comprobar que nos llega una acción válida
$action = $_GET['action'] ?? $data['action'] ?? null; // Busca si se ha enviado una acción. Si no existe en la URL ni en el JSON, le asigna null.

if (!$action) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "No se ha especificado ninguna acción"
    ]);
    exit;
}

// Enrutador de acciones
switch ($action) {
    case 'login': // Si JavaScript envió la acción "login"
        procesarLogin($data);
        break;

    case 'registro':
        procesarRegistro($data);
        break;

    case 'logout':
        procesarLogout();
        break;

    case 'session': // Comprueba si hay una sesión activa
        procesarSession();
        break;

    default: // Si envían una acción inventada
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Acción no válida"
        ]);
        break;
}

function procesarLogin($data) {
    $email = $data['email'] ?? null; // Extrae el email del array. Si falta, le pone null.
    $password = $data['password'] ?? null; // Extrae la contraseña. Si falta, le pone null.

    if (!$email || !$password) { // Si falta el email o la contraseña...
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Email y contraseña son obligatorios"
        ]);
        return;
    }

    $cliente = Cliente::login($email, $password); // Usa el método estático de Cliente. Busca en BD, verifica el hash y devuelve un OBJETO.

    if ($cliente) { // Si el objeto se creó con éxito.
        // Se guarda los datos clave del cliente en la sesión
        $_SESSION['cliente_dni'] = $cliente->getDni();
        $_SESSION['cliente_nombre'] = $cliente->getNombre();

        echo json_encode([ // Responde a JS confirmando el éxito y manda sus datos básicos para la interfaz.
            "status" => "success",
            "message" => "Inicio de sesión correcto",
            "data" => [
                "dni" => $cliente->getDni(),
                "nombre" => $cliente->getNombre(),
                "email" => $cliente->getEmail()
            ]
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode([
            "status" => "error",
            "message" => "Credenciales incorrectas"
        ]);
    }
}

function procesarRegistro($data) {
    // Saca todos los campos del array. Si el usuario dejó alguno en blanco, se quedará como null.
    $dni = $data['dni'] ?? null;
    $nombre = $data['nombre'] ?? null;
    $apellidos = $data['apellidos'] ?? null;
    $telefono = $data['telefono'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$dni || !$nombre || !$apellidos || !$email || !$password) { //Comprueba que los campos obligatorios estén
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Todos los campos obligatorios deben estar cumplimentados"
        ]);
        return;
    }

    // Crea un nuevo objeto Cliente en memoria (aún no en BD) pasándole los datos al constructor.
    $nuevoCliente = new Cliente($dni, $nombre, $apellidos, $telefono, $email, $password);

    if ($nuevoCliente->insert()) { // Invoca el método insert(). Si MySQL devuelve true al guardar...
        echo json_encode([ // Responde a JS confirmando que todo ha ido perfecto.
            "status" => "success",
            "message" => "Cliente registrado correctamente"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo completar el registro"
        ]);
    }
}

function procesarLogout() {
    session_destroy(); // Elimina todos los datos de sesión
    echo json_encode([ // Le confirma al frontend que la sesión se cerró para que pueda enviarlo al login.
        "status" => "success",
        "message" => "Sesión cerrada correctamente"
    ]);
}

function procesarSession() {
    // Comprueba si el "cajón" del DNI existe en la memoria del servidor
    if (isset($_SESSION['cliente_dni'])) { 
        echo json_encode([
            "status" => "success",
            "message" => "Sesión activa",
            "data" => [
                "nombre" => $_SESSION['cliente_nombre']
            ]
        ]);
    } else {
        http_response_code(401); // 401: No autorizado
        echo json_encode([
            "status" => "error",
            "message" => "No hay sesión activa"
        ]);
    }
}
?>