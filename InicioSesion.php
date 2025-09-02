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

// Validar parámetros
if (empty($_POST['email']) || empty($_POST['password'])) {
    respuesta("error", "Error", "Faltan parámetros email o password");
}

$x_email = $_POST['email'];
$x_pass  = $_POST['password'];

// Conectar DB
$conexion = mysqli_connect($servidor, $usuario, $pass, $base);
if (!$conexion) {
    respuesta("error", "Error de conexión", "No se pudo conectar con la base de datos");
}

$x_email = mysqli_real_escape_string($conexion, $x_email);

// Buscar usuario por correo o username
$consulta = "SELECT id, Token, username, email, avatar_url, role, password 
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

// Verificar contraseña (si está hasheada con password_hash usa password_verify)
if ($x_pass !== $row['password']) {
    
    // si usas hash: if (!password_verify($x_pass, $row['password'])) { ... }
    respuesta("error", "Error de autenticación", "Contraseña incorrecta", 10);
}

// Datos que React guardará en localStorage
$data = [
    'id'        => $row['id'],
    'Token'   => $row['Token'],
    'username'  => $row['username'],
    'email'     => $row['email'],
    'avatar_url'=> $row['avatar_url'],
    'role'      => $row['role']
];

respuesta("success", "¡Bienvenido, {$row['username']}!", "Autenticación exitosa.", 25, $data);

$stmt->close();
$conexion->close();
?>
