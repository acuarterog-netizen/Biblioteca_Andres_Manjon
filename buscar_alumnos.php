<?php
header('Content-Type: application/json; charset=utf-8');
$conexion = new mysqli("localhost", "root", "", "");

$q = isset($_GET['q']) ? $_GET['q'] : '';

// Buscamos al alumno y traemos su usuario mediante el carnet
$sql = "SELECT A.*, U.username 
        FROM alumnado A 
        LEFT JOIN usuario U ON A.codigo_de_carnet = U.codigo_de_carnet 
        WHERE A.nombre LIKE ? LIMIT 1";

$stmt = $conexion->prepare($sql);
$like = $q . "%";
$stmt->bind_param("s", $like);
$stmt->execute();
$res = $stmt->get_result();

$resultados = [];
while ($row = $res->fetch_assoc()) {
    $resultados[] = $row;
}

echo json_encode($resultados);
