<?php
session_start();

$id_logueado = $_SESSION['usuario_id'] ?? null;
$id_rol = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Libros</title>
    <link rel="icon" href="images/andresmanjonlogo1.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* ESTILOS PARA EL GRID DE TARJETAS */
        .contenedor-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .libro-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .libro-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .card-imagen {
            height: 200px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            padding: 15px;
        }

        .portada-libro {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .card-header-cat {
            background: #f8f9fa;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #eaeaea;
        }

        .cat-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* CORREGIDO: Aqu� hab�a c�digo PHP que romp�a la p�gina porque la variable $item a�n no exist�a */
        .categoria-texto {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .card-body-cat {
            padding: 20px;
            flex-grow: 1;
        }

        .libro-titulo {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 1.1rem;
            line-height: 1.4;
            font-weight: 600;
        }

        .libro-info {
            margin: 8px 0;
            color: #666;
            font-size: 0.9rem;
        }

        .disponibilidad {
            margin-top: 15px;
            font-weight: 600;
        }

        .card-footer-cat {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-detalle {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .btn-detalle:hover {
            background: #0056b3;
        }

        .id-text {
            color: #888;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .contenedor-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .contenedor-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav>
        <img class="centro" src="images/andresmanjonlogo2.png" width="130" height="40">

        <ul class="menubib">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="biblioteca.php">Biblioteca</a></li>
            <?php if ($id_rol === 1 || $id_rol === 2): ?>
                <li><a href="peticion.php">Prestamos</a></li>
                <li><a href="anadir.html">Añadir</a></li>
                <li><a href="alumnoslista.php">Lista Alumnado</a></li>
            <?php endif; ?>
            <?php if ($id_rol === 3) : ?> 
                <li><a href="perfil.php">Mi Perfil</a></li>
            <?php endif; ?>

            <?php if ($id_rol === 2) :?>
                <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>


            <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
            <li><a href="bib.php"><button type="button" class="btnomar">Biblioteca</button></a></li>
            
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
$bd = "sistemabiblioteca";
$puerto = 3306; 

$conexion = new mysqli($host, $usuario, $clave, $bd, $puerto);

// Verificar conexi�n
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Construir la consulta con filtros
$sql = "SELECT * FROM libro WHERE 1=1";
$params = [];
$types = "";

// Filtro de b�squeda
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $sql .= " AND (titulo LIKE ? OR autor LIKE ? OR editorial LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Filtro de categor�as
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

// Mapa de categorías a imágenes
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
?>

<div class="contenedor-grid">
<?php
if ($resultado && $resultado->num_rows > 0):
    
    while($item = $resultado->fetch_assoc()): 
        
        // Obtener ID del libro
        $id_actual = $item['id'] 
            ?? $item['id_libro'] 
            ?? $item['id_libros'] 
            ?? $item['libro_id'] 
            ?? 0;

        // Estado y color
        $estado = strtolower(trim($item['estado_de_actividad']));
        $esDisponible = strpos($estado, 'disponible') !== false;
        $colorEstado = $esDisponible ? '#28a745' : '#dc3545';

        // Categor�a
        $categoria = trim($item['ubicacion_por_colores']);
        $categoriaKey = strtolower($categoria);
        
        // Obtener imagen de categoría
        $imgCategoria = $catToImg[$categoriaKey] ?? "default.png";
?>
    
    <div class="libro-card">
        <div class="card-header-cat">
            <img src="images/<?php echo htmlspecialchars($imgCategoria); ?>" class="cat-icon" alt="<?php echo htmlspecialchars($categoria); ?>">
            <span class="categoria-texto"><?php echo htmlspecialchars($categoria); ?></span>
        </div>

        <div class="card-imagen" style="height: 300px; background: #eee; overflow: hidden;">
            <?php 
                // Comprobar si hay imagen, si no usar default
                $imgLibro = !empty($item['imagen']) ? $item['imagen'] : 'default.jpg';
            ?>
            <img src="img/libros/<?php echo $imgLibro; ?>" 
                 alt="Portada" 
                 style="width: 100%; height: 100%; object-fit: cover;">
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

            <p class="libro-info">
                <strong>ISBN:</strong> 
                <?php echo htmlspecialchars($item['isbn']); ?>
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
    <p style="text-align:center; width:100%; grid-column: 1 / -1; padding: 40px;">
        No se encontraron libros con estos filtros.
        <?php if(isset($_GET['search']) || isset($_GET['categorias']) || isset($_GET['disponibilidad'])): ?>
            <br>
            <a href="biblioteca.php" class="btn" style="margin-top: 10px;">Ver todos los libros</a>
        <?php endif; ?>
    </p>
<?php endif; ?>
</div>

<?php
// Mostrar contador de resultados
if ($resultado && $resultado->num_rows > 0) {
    echo "<p style='text-align: center; margin: 20px;'>Mostrando " . $resultado->num_rows . " libro(s)</p>";
}

$stmt->close();
$conexion->close();
?>
</body>
</html>
