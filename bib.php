<?php
session_start();

// 1. Obtenemos el rol si existe, si no, queda como null
$id_rol = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : null;
$id_logueado = $_SESSION['usuario_id'] ?? null;

// Nota: Se ha eliminado el redireccionamiento forzoso para permitir que usuarios sin rol vean la página.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Libros</title>
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

            <?php if ($id_rol): ?>
                <li><a href="perfil.php">Mi Perfil</a></li>
            <?php if ($id_rol === 2) :?>
                <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>

                <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
            <?php else: ?>
                <li><a href="inicio.php"><button type="button" class="btn">Iniciar Sesión</button></a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" id="search" name="search" placeholder="Buscar por título, autor o editorial..." 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn">Buscar</button>
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="biblioteca.php" class="btn" style="margin-left: 10px;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <form method="GET" action="" class="search-container" id="categoryMenu">
        <span>Categorías:</span>
        <?php
        $categorias = [
            'verde' => 'Infantil y 1º Ciclo',
            'naranja' => '2º y 3º Ciclo',
            'cian' => 'Animales y Naturaleza',
            'rojo' => 'Valores',
            'rosa' => 'Emociones',
            'morado' => 'Igualdad',
            'amarillo' => 'Inglés',
            'marron' => 'Colecciones',
            'blanco' => 'Cómics',
            'negro' => 'Música'
        ];
        
        foreach($categorias as $valor => $nombre): 
            $checked = isset($_GET['categorias']) && in_array($valor, $_GET['categorias']) ? 'checked' : '';
        ?>
            <label>
                <input type="checkbox" name="categorias[]" value="<?php echo $valor; ?>" <?php echo $checked; ?> 
                       onchange="this.form.submit()"> <?php echo $nombre; ?>
            </label>
        <?php endforeach; ?>
    </form>

    <form method="GET" action="" class="search-container" id="categoryMenu">
        <span>Disponibilidad:</span>
        <?php
        $disponibilidades = [
            'Disponible' => 'Disponible',
            'No Disponible' => 'No Disponible'
        ];
        
        foreach($disponibilidades as $valor => $nombre): 
            $checked = isset($_GET['disponibilidad']) && in_array($valor, $_GET['disponibilidad']) ? 'checked' : '';
        ?>
            <label>
                <input type="checkbox" name="disponibilidad[]" value="<?php echo $valor; ?>" <?php echo $checked; ?> 
                       onchange="this.form.submit()"> <?php echo $nombre; ?>
            </label>
        <?php endforeach; ?>
        
        <?php if(isset($_GET['categorias']) || isset($_GET['disponibilidad'])): ?>
            <a href="biblioteca.php" class="btn" style="margin-left: 10px;">Limpiar Filtros</a>
        <?php endif; ?>
    </form>
    
<?php
$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306;     

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT * FROM libro WHERE 1=1";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (titulo LIKE ? OR autor LIKE ? OR editorial LIKE ?)";
    $params[] = $search; $params[] = $search; $params[] = $search;
    $types .= "sss";
}

if (isset($_GET['categorias']) && is_array($_GET['categorias']) && !empty($_GET['categorias'])) {
    $placeholders = str_repeat('?,', count($_GET['categorias']) - 1) . '?';
    $sql .= " AND LOWER(TRIM(ubicacion_por_colores)) IN ($placeholders)";
    $types .= str_repeat('s', count($_GET['categorias']));
    foreach ($_GET['categorias'] as $categoria) {
        $params[] = strtolower(trim($categoria));
    }
}

if (isset($_GET['disponibilidad']) && is_array($_GET['disponibilidad']) && !empty($_GET['disponibilidad'])) {
    $dispoPlaceholders = str_repeat('?,', count($_GET['disponibilidad']) - 1) . '?';
    $sql .= " AND LOWER(TRIM(estado_de_actividad)) IN ($dispoPlaceholders)";
    $types .= str_repeat('s', count($_GET['disponibilidad']));
    foreach ($_GET['disponibilidad'] as $dispo) {
        $params[] = strtolower(trim($dispo));
    }
}

$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$catToImg = [
    'verde' => 'infantily1ciclo.png', 'naranja' => '2y3ciclo.png', 'cian' => 'animalesnaturaleza.png',
    'rojo' => 'valores.png', 'rosa' => 'emociones.png', 'morado' => 'igualdad.png',
    'amarillo' => 'ingles.png', 'marron' => 'colecciones.png', 'blanco' => 'comics.png', 'negro' => 'musica.png',
];

function renderCategoryImage($val, $map) {
    if (empty($val)) return '';
    $key = strtolower(trim($val));
    if (isset($map[$key])) {
        $file = $map[$key];
        return '<img src="images/' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '" width="20" height="20" alt="">';
    }
    return '<span>' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '</span>';
}

if ($resultado && $resultado->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo "<table border='1' cellpadding='5' width='100%'>";
    echo "<tr><th>Título</th><th>Autor/a</th><th>Editorial</th><th>ISBN</th><th>Clasificación</th><th>Disponibilidad</th></tr>";

    while ($fila = $resultado->fetch_assoc()) {
        $catHtml = renderCategoryImage($fila['ubicacion_por_colores'], $catToImg);
        $dispText = 'N/A';
        if (isset($fila['estado_de_actividad'])) {
            $estado = strtolower(trim($fila['estado_de_actividad']));
            $dispText = ($estado === 'disponible') ? '<span style="color:green;">Disponible</span>' : '<span style="color:red;">No Disponible</span>';
        }
        
        echo "<tr>
                <td>" . htmlspecialchars($fila['titulo'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($fila['autor'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($fila['editorial'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($fila['isbn'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . $catHtml . "</td>
                <td>" . $dispText . "</td>
              </tr>";
    }
    echo "</table></div>";
    echo "<p style='text-align: center; margin: 20px;'>Mostrando " . $resultado->num_rows . " libro(s)</p>";
} else {
    echo "<div style='text-align: center; padding: 40px;'><h3>No se encontraron libros</h3></div>";
}

$stmt->close();
$conexion->close();
?>
</body>
</html>
