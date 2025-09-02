<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: content-type");
header("Access-Control-Allow-Methods: OPTIONS,GET,PUT,POST,DELETE");
header("Content-Type: application/json; charset=utf-8");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

$servidor = 'fdb1032.awardspace.net';
$usuario = '4677286_daec1901';
$pass = 'ihO6Kg58t70sti';
$base = '4677286_daec1901';
$tabla = "users";

$conn = new mysqli($servidor, $usuario, $pass, $base);
if ($conn->connect_error) {
    echo json_encode(["Icon" => "error", "Mensaje" => "Error de conexión"]);
    exit;
}

date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d H:i:s');

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$avatar_url = trim($_POST['avatar_url'] ?? '');

// Si no hay avatar enviado, poner el predeterminado
if (!$avatar_url) {
    $avatar_url = "/IconPerfil.gif";
}

// Validar campos requeridos
if (!$username || !$email || !$password) {
    echo json_encode(["Icon" => "error", "Mensaje" => "Faltan datos requeridos"]);
    exit;
}

// Verificar si el usuario ya existe
$checkUser = $conn->prepare("SELECT id FROM $tabla WHERE username=?");
$checkUser->bind_param("s", $username);
$checkUser->execute();
$checkUser->store_result();
if ($checkUser->num_rows > 0) {
    echo json_encode(["Icon" => "error", "Mensaje" => "El usuario ya existe"]);
    exit;
}
$checkUser->close();

// Verificar si el correo ya existe
$checkEmail = $conn->prepare("SELECT id FROM $tabla WHERE email=?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();
if ($checkEmail->num_rows > 0) {
    echo json_encode(["Icon" => "error", "Mensaje" => "El correo ya está registrado"]);
    exit;
}
$checkEmail->close();

// Guardar la contraseña sin hash (no recomendado para producción)
$storedPassword = $password;

// Función para generar token aleatorio
function generarToken($length = 15)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $token;
}

// Generar token único
do {
    $token = generarToken();
    $checkToken = $conn->query("SELECT token FROM $tabla WHERE token='$token'");
} while ($checkToken->num_rows > 0);

// Insertar usuario en la base de datos
$stmt = $conn->prepare("INSERT INTO $tabla (username,email,password,avatar_url,role,token,created_at,updated_at) VALUES (?,?,?,?, 'user', ?, ?, ?)");
$stmt->bind_param("sssssss", $username, $email, $storedPassword, $avatar_url, $token, $fechaActual, $fechaActual);

if ($stmt->execute()) {
    echo json_encode([
        "Icon" => "success",
        "Mensaje" => "Registro exitoso",
        "Data" => [
            "token" => $token,
        ]
    ]);
} else {
    echo json_encode(["Icon" => "error", "Mensaje" => "Error al guardar usuario"]);
}

$stmt->close();
$conn->close();
?>