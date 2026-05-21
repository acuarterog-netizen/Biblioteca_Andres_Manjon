<?php

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306; 
$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
else{
    echo "CONEXIÓN EXITOSA A LA BASE DE DATOS MEDIANTE LA CONEXIÓN.PHP";
}
?>