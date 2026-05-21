<?php
// Mover el inicio de sesión al principio para evitar errores de cabeceras
session_start();

// 1. Verificación de seguridad y acceso
// Si el rol es 3 (Alumno) o no hay sesión, no puede entrar y se redirige a inicio.php
if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] === 3) {
    header("Location: inicio.php");
    exit();
}

$id_rol = (int)$_SESSION['id_rol'];

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306; 

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// OBTENER TODOS LOS ALUMNOS
$alumnos = [];
$sql_alumnos = "SELECT id_alumnado, nombre, apellidos, codigo_de_carnet FROM alumnado ORDER BY nombre ASC";
$stmt_al = $conexion->prepare($sql_alumnos);
$stmt_al->execute();
$res_al = $stmt_al->get_result();
while ($al = $res_al->fetch_assoc()) {
    $alumnos[] = $al;
}
$stmt_al->close();

// Variables para mantener los valores del formulario
$titulo_val = isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : '';
$autor_val = isset($_GET['autor']) ? htmlspecialchars($_GET['autor']) : '';
$id_libro_val = isset($_GET['id_libro']) ? htmlspecialchars($_GET['id_libro']) : '';

// Manejo de mensajes de error
$mensaje_error = "";
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_libro': $mensaje_error = "Debes seleccionar un libro de las sugerencias antes de enviar."; break;
        case 'no_disponible': $mensaje_error = "El libro seleccionado ya no está disponible."; break;
        case 'no_alumno': $mensaje_error = "Por favor, selecciona un alumno de la lista."; break;
        case 'no_usuario': $mensaje_error = "El carnet del alumno no coincide con ninguna cuenta de usuario."; break;
    }
}

// Buscar sugerencias de libros
$sugerencias = [];
if (isset($_GET['buscar_titulo']) && !empty($titulo_val)) {
    $sql = "SELECT id_libro, titulo, autor FROM libro WHERE titulo LIKE ? ORDER BY titulo ASC LIMIT 15";
    $stmt = $conexion->prepare($sql);
    $busqueda = "%" . $titulo_val . "%";
    $stmt->bind_param("s", $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $sugerencias[] = $fila;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo de Libros</title>
    <link rel="icon" href="images/andresmanjonlogo1.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav> 
        <img class="centro" src="images/andresmanjonlogo2.png" width="130" height="40">
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="biblioteca.php">Biblioteca</a></li>
            <li><a href="peticion.php">Prestamos</a></li>
            <li><a href="anadir.php">Añadir</a></li>
            <li><a href="alumnoslista.php">Lista Alumnado</a></li>
            
            <li><a href="<?php echo ($id_rol === 1) ? 'perfil_admin.php' : 'perfil.php'; ?>">Mi Perfil</a></li>


             <?php if ($id_rol=== 2) :?>
            <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>
            
            <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
        </ul>
    </nav>

<div class="container">
    <h1>Préstamo de Libros</h1>

    <?php if ($mensaje_error): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
            <strong>¡Atención!</strong> <?php echo $mensaje_error; ?>
        </div>
    <?php endif; ?>
    
    <form method="GET" action="" style="margin-bottom: 20px;">
        <label for="titulo_buscar">Buscar libro por título:</label>
        <div class="titulo-wrapper">
            <input type="text" id="titulo_buscar" name="titulo" 
                   value="<?php echo $titulo_val; ?>" 
                   placeholder="Escribe el título del libro..." required>
            <input type="hidden" name="buscar_titulo" value="1">
        </div>
        <button type="submit" class="btn2" style="margin-top: 10px;">Buscar Sugerencias</button>
    </form>
    
    <?php if (!empty($sugerencias)): ?>
    <div style="margin-bottom: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
        <h3>Libros encontrados:</h3>
        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px;">
            <?php foreach ($sugerencias as $libro): ?>
            <form method="GET" action="" style="margin: 5px 0;">
                <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>">
                <input type="hidden" name="autor" value="<?php echo htmlspecialchars($libro['autor']); ?>">
                <input type="hidden" name="id_libro" value="<?php echo htmlspecialchars($libro['id_libro']); ?>">
                <button type="submit" class="btn" style="width: 100%; text-align: left; background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; margin-bottom: 5px;">
                    <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong> - <?php echo htmlspecialchars($libro['autor']); ?>
                </button>
            </form>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <form action="procesar_peticion.php" method="post">
        <label for="id_alumnado">Seleccionar Alumno:</label>
        <select id="id_alumnado" name="alumno_info" required style="width: 100%; padding: 8px; margin-bottom: 15px;">
            <option value="">-- Selecciona un alumno de la lista --</option>
            <?php foreach ($alumnos as $alumno): ?>
                <option value="<?php echo $alumno['id_alumnado'] . '|' . $alumno['codigo_de_carnet']; ?>">
                    <?php echo htmlspecialchars($alumno['nombre'] . " " . $alumno['apellidos']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="titulo">Título del Libro:</label>
        <input type="text" id="titulo" name="titulo" value="<?php echo $titulo_val; ?>" required readonly>
        
        <label for="autor">Autor del Libro:</label>
        <input type="text" id="autor" name="autor" value="<?php echo $autor_val; ?>" required readonly>
        
        <input type="hidden" id="id_libro" name="id_libro" value="<?php echo $id_libro_val; ?>">
        
        <button type="submit" class="btn2">Enviar Petición</button>
    </form>
</div>

<?php $conexion->close(); ?>
</body>
</html>