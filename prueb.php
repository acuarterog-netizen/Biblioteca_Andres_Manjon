<?php
session_start();

// 1. Verificación de seguridad básica
if (!isset($_SESSION['usuario_id']) || $_SESSION['id_rol'] !== 1 && $_SESSION['id_rol'] !== 2 && $_SESSION['id_rol'] !== 3) {
    header("Location: index.php");
    exit();
}
$id_logueado = $_SESSION['usuario_id'];
$id_rol = (int)$_SESSION['id_rol'];

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
            <li><a href="inicio.html">Inicio</a></li>
            <li><a href="biblioteca.php">Biblioteca</a></li>
            <?php if ($id_rol === 1 || $id_rol === 2): ?>
                <li><a href="peticion.php">Prestamos</a></li>
                <li><a href="anadir.html">Añadir</a></li>
                <li><a href="alumnoslista.php">Lista Alumnado</a></li>
            <?php endif; ?>
            <li><a href="perfil.php">Mi Perfil</a></li>
            <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
        </ul>
    </nav>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" id="search"name="search" placeholder="Buscar por título, autor o editorial..." 
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
$bd = "";
$puerto = 3306;  

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Construir la consulta con filtros
$sql = "SELECT * FROM libro WHERE 1=1";
$params = [];
$types = "";

// Filtro de búsqueda
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (titulo LIKE ? OR autor LIKE ? OR editorial LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Filtro de categorías
if (isset($_GET['categorias']) && is_array($_GET['categorias']) && !empty($_GET['categorias'])) {
    $placeholders = str_repeat('?,', count($_GET['categorias']) - 1) . '?';
    $sql .= " AND LOWER(TRIM(ubicacion_por_colores)) IN ($placeholders)";
    $types .= str_repeat('s', count($_GET['categorias']));
    foreach ($_GET['categorias'] as $categoria) {
        $params[] = strtolower(trim($categoria));
    }
}

// Filtro de disponibilidad
if (isset($_GET['disponibilidad']) && is_array($_GET['disponibilidad']) && !empty($_GET['disponibilidad'])) {
    $dispoPlaceholders = str_repeat('?,', count($_GET['disponibilidad']) - 1) . '?';
    $sql .= " AND LOWER(TRIM(estado_de_actividad)) IN ($dispoPlaceholders)";
    $types .= str_repeat('s', count($_GET['disponibilidad']));
    foreach ($_GET['disponibilidad'] as $dispo) {
        $params[] = strtolower(trim($dispo));
    }
}

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Map database category values to image filenames
$catToImg = [
    'verde' => 'infantily1ciclo.png',
    'naranja' => '2y3ciclo.png',
    'cian' => 'animalesnaturaleza.png',
    'rojo' => 'valores.png',
    'rosa' => 'emociones.png',
    'morado' => 'igualdad.png',
    'amarillo' => 'ingles.png',
    'marron' => 'colecciones.png',
    'blanco' => 'comics.png',
    'negro' => 'musica.png',
];

function renderCategoryImage($val, $map) {
    if (empty($val)) return '';
    $key = strtolower(trim($val));
    if (isset($map[$key])) {
        $file = $map[$key];
        $localPath = __DIR__ . '/images/' . $file;
        if (file_exists($localPath)) {
            return '<img src="images/' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '" width="20" height="20" alt="">';
        }
    }
    return '<span>' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '</span>';
}
?>




<div class="contenedor-grid">
<?php
// Verificamos que la consulta devolvió resultados
if ($resultado && $resultado->num_rows > 0):

    while($item = $resultado->fetch_assoc()): 
        
        // -------------------------------
        // 1. CORRECCIÓN DE ID
        // -------------------------------
        // Ajusta aquí según el nombre REAL de tu columna en la BD
        $id_actual = $item['id'] 
            ?? $item['id_libro'] 
            ?? $item['id_libros'] 
            ?? $item['libro_id'] 
            ?? 0;

        // -------------------------------
        // 2. ESTADO Y COLOR
        // -------------------------------
        $estado = strtolower(trim($item['estado_de_actividad']));
        $esDisponible = strpos($estado, 'disponible') !== false;
        $colorEstado = $esDisponible ? '#28a745' : '#dc3545';

        // -------------------------------
        // 3. CATEGORÍA POR COLORES
        // -------------------------------
        $categoria = trim($item['ubicacion_por_colores']);

        // Normalizamos para evitar problemas con tildes o mayúsculas
        $categoriaKey = strtolower($categoria);

        // Mapea tus colores reales
        $catToImg = [
            "negro"  => "negro.png",
            "rojo"   => "rojo.png",
            "marron" => "marron.png",
            "marrón" => "marron.png"
        ];

        // Si no existe, evita errores
        $imgCategoria = $catToImg[$categoriaKey] ?? "default.png";
?>
    
    <div class="libro-card">

        <div class="card-header-cat">
            <img src="img/categorias/<?php echo $imgCategoria; ?>" class="cat-icon">
            <span class="categoria-texto"><?php echo htmlspecialchars($categoria); ?></span>
        </div>

        <div class="card-body-cat">
            <h3 class="libro-titulo"><?php echo htmlspecialchars($item['titulo']); ?></h3>

            <p class="libro-info">
                <strong>Autor:</strong> 
                <?php echo htmlspecialchars($item['autor']); ?>
            </p>

            <p class="libro-info">
                <strong>Editorial:</strong> 
                <?php echo htmlspecialchars($item['editorial']); ?>
            </p>

            <p class="disponibilidad" style="color: <?php echo $colorEstado; ?>;">
                <strong><?php echo ucfirst($estado); ?></strong>
            </p>
        </div>

        <div class="card-footer-cat">
            <a href="detalle_libro.php?id=<?php echo $id_actual; ?>" class="btn-detalle">
                Ver Detalles
            </a>
            <small class="id-text">ID: <?php echo $id_actual; ?></small>
        </div>

    </div>

<?php 
    endwhile;

else:
?>
    <p style="text-align:center; width:100%;">No se encontraron libros con estos filtros.</p>
<?php endif; ?>
</div>


