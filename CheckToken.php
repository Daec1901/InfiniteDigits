<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: content-type");
header("Access-Control-Allow-Methods: OPTIONS,GET,PUT,POST,DELETE");
header("Content-Type: application/json; charset=utf-8");

// Evitar que warnings rompan JSON
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Configuración de la base de datos
$servidor = 'fdb1032.awardspace.net';
$base     = '4677286_daec1901';
$usuario  = '4677286_daec1901';
$pass     = 'ihO6Kg58t70sti';
$tabla    = 'users';

// Función para responder JSON
function respuesta($icon, $titulo, $mensaje, $data = null) {
    $resp = [
        "Icon" => $icon,
        "Titulo" => $titulo,
        "Mensaje" => $mensaje
    ];
    if ($data) $resp["Data"] = $data;
    echo json_encode($resp);
    exit;
}

// Validar token/email
if (empty($_POST['email'])) {
    respuesta("error", "Error", "Falta el parámetro 'email' o token.");
}

$token = $_POST['email'];

// Conectar a la base de datos
$conn = new mysqli($servidor, $usuario, $pass, $base);
if ($conn->connect_error) {
    respuesta("error", "Error de conexión", "No se pudo conectar a la base de datos.");
}

// Escapar parámetro
$token = $conn->real_escape_string($token);

// Consulta para obtener los datos del usuario
$sql = "SELECT username, email, avatar_url, role FROM $tabla WHERE email = ? OR token = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    respuesta("error", "Error SQL", "No se pudo preparar la consulta.");
}

$stmt->bind_param("ss", $token, $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    respuesta("error", "Usuario no encontrado", "El token o email no es válido.");
}

// Responder con los datos del usuario
respuesta("success", "Usuario válido", "Datos obtenidos correctamente.", $user);

$stmt->close();
$conn->close();
?>
