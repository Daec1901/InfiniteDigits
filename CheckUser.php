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
$tabla    = "users";

function respuesta($icon, $titulo, $mensaje, $alerta = 5, $extra = null) {
    $resp = [
        "Icon" => $icon,
        "Titulo" => $titulo,
        "Mensaje" => $mensaje,
        "Alerta" => $alerta
    ];
    if ($extra) $resp["Data"] = $extra;
    echo json_encode($resp);
    exit;
}

// Validar parámetro
if (empty($_POST['email'])) {
    respuesta("error", "Error", "Falta parámetro email o usuario");
}

$x_email = $_POST['email'];

// Conectar DB
$conexion = mysqli_connect($servidor, $usuario, $pass, $base);
if (!$conexion) {
    respuesta("error", "Error de conexión", "No se pudo conectar con la base de datos");
}

$x_email = mysqli_real_escape_string($conexion, $x_email);

// Buscar por correo o username
$consulta = "SELECT username, email, avatar_url, role 
             FROM $tabla 
             WHERE email = ? OR username = ?";
$stmt = $conexion->prepare($consulta);
if (!$stmt) {
    respuesta("error", "Error SQL", "No se pudo preparar la consulta");
}
$stmt->bind_param("ss", $x_email, $x_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    respuesta("error", "Error de autenticación", "Usuario no encontrado");
}

$data = [
    'username'  => $row['username'],
    'avatar_url'=> $row['avatar_url'],
];

respuesta("success", "Usuario válido", "Usuario encontrado, continua con la contraseña.", 15, $data);

$stmt->close();
$conexion->close();
?>
