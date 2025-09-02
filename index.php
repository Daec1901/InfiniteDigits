<?php
// index.php

// Establece la zona horaria
date_default_timezone_set('America/Mexico_City');

// Mensaje de bienvenida
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Página de Prueba PHP</title>
</head>
<body>
    <h1>¡Hola! Esto es un PHP funcionando correctamente.</h1>
    <p>La fecha y hora actual es: " . date('d/m/Y H:i:s') . "</p>
</body>
</html>";
?>
