<?php
session_start();

// 1. Verificación de seguridad básica
if (!isset($_SESSION['usuario_id']) || $_SESSION['id_rol'] !== 1 && $_SESSION['id_rol'] !== 2 && $_SESSION['id_rol'] !== 3) {
    header("Location: index.php");
    exit();
}

$id_logueado = $_SESSION['usuario_id'];
$id_rol = (int)$_SESSION['id_rol'];

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306; 

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

if ($id_rol === 1 || $id_rol === 2) { 
    // --- CASO ADMIN (1) O PROFESOR (2): Solo tabla Usuario ---
    $sql = "SELECT username, nombre, codigo_de_carnet FROM usuario WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_logueado);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();
    
    // Creamos etiquetas genéricas para que el HTML no falle
    $nombre_completo = $datos['nombre'];
    $subtitulo = "Personal del Centro";
    $extra_info = "Código de Identificación: " . $datos['codigo_de_carnet'];
} 
else {
    // --- CASO ALUMNO (3): Relación con Alumnado ---
    $sql = "SELECT u.username, a.nombre, a.apellidos, a.clase 
            FROM usuario u
            INNER JOIN alumnado a ON u.codigo_de_carnet = a.codigo_de_carnet
            WHERE u.id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_logueado);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();

    if ($datos) {
        $nombre_completo = $datos['nombre'] . " " . $datos['apellidos'];
        $subtitulo = "Curso: " . $datos['clase'];
        $extra_info = "Usuario: " . $datos['username'];
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
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
            <li><a href="perfil.php">Mi Perfil</a></li>

            <?php if ($id_rol=== 2) :?>
            <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>
            <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
        </ul>
    </nav>

<div class="container2">
    <?php if (isset($datos)): ?>
        <h2>Perfil de <?php echo ($_SESSION['id_rol'] == 1) ? 'Administrador' : (($_SESSION['id_rol'] == 2) ? 'Profesor' : 'Alumno'); ?></h2>
        
        <div class="card">
            <p><b>Nombre:</b> <?php echo htmlspecialchars($nombre_completo); ?></p>
            <p><b>Estado:</b> <?php echo htmlspecialchars($subtitulo); ?></p>
            <p><b>Detalles:</b> <?php echo htmlspecialchars($extra_info); ?></p>
        </div>
    <?php else: ?>
        <p>Error: No se han encontrado datos para este perfil.</p>
    <?php endif; ?>
</div>
<br>
<div class="container2">
    <h1>Mis préstamos</h1>
    
    <?php
    // Verificar conexión (ya debería estar abierta de arriba)
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Consulta para obtener los préstamos del usuario actual con información relacionada
    if ($id_rol === 1 || $id_rol === 2) {
        // Para administradores/profesores: mostrar todos los préstamos o solo los propios según necesidad
        // Aquí muestro solo los propios, pero puedes cambiar si quieres que vean todos
        $sql_prestamos = "
            SELECT 
                p.id_prestamo,
                l.titulo as titulo_libro,
                u.nombre as nombre_usuario,
                p.fecha_de_salida,
                p.fecha_de_devolucion,
                p.estado_del_prestamo
            FROM prestamo p
            INNER JOIN libro l ON p.id_libro = l.id_libro
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE p.id_usuario = ?
            ORDER BY p.fecha_de_salida DESC
        ";
        $param = $id_logueado;
    } else {
        // Para alumnos: mostrar solo sus préstamos
        $sql_prestamos = "
            SELECT 
                p.id_prestamo,
                l.titulo as titulo_libro,
                CONCAT(a.nombre, ' ', a.apellidos) as nombre_usuario,
                p.fecha_de_salida,
                p.fecha_de_devolucion,
                p.estado_del_prestamo
            FROM prestamo p
            INNER JOIN libro l ON p.id_libro = l.id_libro
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario
            INNER JOIN alumnado a ON u.codigo_de_carnet = a.codigo_de_carnet
            WHERE p.id_usuario = ?
            ORDER BY p.fecha_de_salida DESC
        ";
        $param = $id_logueado;
    }
    
    $stmt_prestamos = $conexion->prepare($sql_prestamos);
    $stmt_prestamos->bind_param("i", $param);
    $stmt_prestamos->execute();
    $resultado_prestamos = $stmt_prestamos->get_result();
    
    // Mostrar resultados en tabla HTML
    if ($resultado_prestamos && $resultado_prestamos->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo "<table border='1' cellpadding='8' width='100%'>";
        echo "<tr>
                <th>Título del Libro</th>
                <th>Usuario</th>
                <th>Fecha de Préstamo</th>
                <th>Fecha de Devolución</th>
                <th>Estado</th>
              </tr>";
        
        while ($fila = $resultado_prestamos->fetch_assoc()) {
            // Formatear fechas para mejor visualización
            $fecha_salida = !empty($fila['fecha_de_salida']) ? date('d/m/Y', strtotime($fila['fecha_de_salida'])) : 'No especificada';
            $fecha_devolucion = !empty($fila['fecha_de_devolucion']) ? date('d/m/Y', strtotime($fila['fecha_de_devolucion'])) : 'Pendiente';
            
            // Color según el estado del préstamo
            $estado_color = '';
            $estado_texto = htmlspecialchars($fila['estado_del_prestamo'], ENT_QUOTES, 'UTF-8');
            
            if (strtolower($estado_texto) === 'activo' || strtolower($estado_texto) === 'pendiente') {
                $estado_color = 'style="color: orange; font-weight: bold;"';
            } elseif (strtolower($estado_texto) === 'devuelto' || strtolower($estado_texto) === 'completado') {
                $estado_color = 'style="color: green; font-weight: bold;"';
            } elseif (strtolower($estado_texto) === 'retrasado' || strtolower($estado_texto) === 'atrasado') {
                $estado_color = 'style="color: red; font-weight: bold;"';
            }
            
            echo "<tr>
                    <td>" . htmlspecialchars($fila['titulo_libro'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlspecialchars($fila['nombre_usuario'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . $fecha_salida . "</td>
                    <td>" . $fecha_devolucion . "</td>
                    <td><span " . $estado_color . ">" . $estado_texto . "</span></td>
                  </tr>";
        }
        echo "</table>";
        echo '</div>';
        
        // Mostrar contador de préstamos
        echo "<p style='text-align: center; margin: 20px; font-weight: bold;'>Total de préstamos: " . $resultado_prestamos->num_rows . "</p>";
        
    } else {
        echo "<div style='text-align: center; padding: 30px; background-color: #f8f9fa; border-radius: 8px;'>";
        echo "<h3 style='color: #6c757d;'>No tienes préstamos activos</h3>";
        echo "<p>No se encontraron préstamos registrados a tu nombre.</p>";
    }
    
    $stmt_prestamos->close();
    ?>
</div>

<?php
// Cerrar conexiones
if (isset($stmt)) {
    $stmt->close();
}
$conexion->close();
?>
</body>
</html>
