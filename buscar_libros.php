<?php
header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306;  

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);
if ($conexion->connect_error) {
    echo json_encode([]);
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = 10;


if ($q === '') {
    $stmt = $conexion->prepare("SELECT id_libro, titulo, autor FROM libro WHERE estado_de_actividad = 'Disponible' LIMIT ?");
    $stmt->bind_param('i', $limit);
} else {
    $like = "%" . $q . "%";
    $stmt = $conexion->prepare("SELECT id_libro, titulo, autor FROM libro WHERE estado_de_actividad = 'Disponible' AND (titulo LIKE ? OR autor LIKE ?) LIMIT ?");
    $stmt->bind_param('ssi', $like, $like, $limit);
}

$results = [];
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = [
            'id' => $row['id_libro'], 
            'titulo' => $row['titulo'],
            'autor' => $row['autor']
        ];
    }
    $stmt->close();
}

$conexion->close();

echo json_encode($results, JSON_UNESCAPED_UNICODE);