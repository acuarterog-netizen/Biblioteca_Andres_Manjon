<?php
session_start();

// 1. Bloqueo de seguridad: Solo Admin (1) y Profesor (2) pueden acceder.
// Si no hay sesión o el rol es 3 (Alumno), se redirige a inicio.php.
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] === 3) {
    header("Location: inicio.php");
    exit();
}

$id_rol = (int)$_SESSION['id_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Alumnado</title>
    <link rel="icon" href="images/andresmanjonlogo1.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <img class="centro" src="images/andresmanjonlogo2.png" width="130" height="40">

        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="biblioteca.php">Biblioteca</a></li>
            
            <?php if ($id_rol === 1 || $id_rol === 2): ?>
                <li><a href="peticion.php">Prestamos</a></li>
                <li><a href="anadir.php">Añadir</a></li>
                <li><a href="alumnoslista.php">Lista Alumnado</a></li>
            <?php endif; ?>

            <li>
                <a href="<?php echo ($id_rol === 1) ? 'perfil_admin.php' : 'perfil.php'; ?>">
                    Mi Perfil
                </a>
            </li>

            <?php if ($id_rol=== 2) :?>
            <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
        </ul>
    </nav>

<div class="container2">
    <h1>Lista de Alumnado</h1>
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

$sql = "SELECT * FROM alumnado";
$resultado = $conexion->query($sql);

// Mostrar resultados en tabla HTML
if ($resultado && $resultado->num_rows > 0) {
    echo "<table border='1' cellpadding='5' width='100%'>";
    echo "<tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Clase</th>
            <th>Código de Carnet</th>
            <th>Sanciones</th>
          </tr>";

    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8')."</td>
                <td>".htmlspecialchars($fila['apellidos'], ENT_QUOTES, 'UTF-8')."</td>
                <td>".htmlspecialchars($fila['clase'], ENT_QUOTES, 'UTF-8')."</td>
                <td>".htmlspecialchars($fila['codigo_de_carnet'], ENT_QUOTES, 'UTF-8')."</td>
                <td>".htmlspecialchars($fila['estado_de_sancion'], ENT_QUOTES, 'UTF-8')."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay registros en la tabla de alumnado.</p>";
}

$conexion->close();
?>
</div>

</body>
</html>