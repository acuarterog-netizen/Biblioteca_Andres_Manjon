<?php
session_start();

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306;   

// Crear la conexión
$conn = new mysqli($host, $usuario, $clave, $bd, $puerto);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtenemos el rol si existe, si no, queda como null
$id_rol = isset($_SESSION['id_rol']) ? (int)$_SESSION['id_rol'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
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
                <?php if ($id_rol === 1): ?>
                    <li><a href="perfil_admin.php">Mi Perfil</a></li>
                <?php else: ?>
                    <li><a href="perfil.php">Mi Perfil</a></li>
                <?php endif; ?>
                
            <?php if ($id_rol=== 2) :?>
            <li><a href="dictado.html">Dictado</a></li>
            <?php endif; ?>

                <li><a href="logout.php"><button type="button" class="btn">Cerrar Sesión</button></a></li>
            <?php else: ?>
                <li><a href="inicio.php"><button type="button" class="btn">Iniciar Sesión</button></a></li>
            <?php endif; ?>
        </ul>
    </nav>

<section class="intro">    
    <h1>Bienvenidos a la Biblioteca del CEIP Andrés Manjón</h1><br>
    <p>Para navegar por el sitio web, seleccione alguna de las páginas en la parte superior de la página.</p>
</section>

<section class="reglas">
    <h1>Reglas de la Biblioteca</h1>
    <p><b>1. USO DE MATERIALES: </b>Respetamos los materiales. No los rompemos, dañamos, pintamos en ellos... Así todos podrán disfrutar de ellos.</p>
    <p><b>2. TIEMPO DE PRÉSTAMO: </b>El tiempo de préstamo es de 1 mes. Deberás devolver el libro en un máximo de un mes desde la fecha de préstamo.</p>
    <p><b>3. FUNCIONAMIENTO DE PRÉSTAMO: </b>Un profesor o bibliotecario escogerá el libro que tú quieras.</p>
    <p><b>4. ¿DETERIORO? </b>Si ves deterioro en un libro que tú no has causado, díselo a un profesor o bibliotecario.</p>
    <p><b>5. CASTIGOS: </b>Por supuesto, si tú has causado daños en un libro, esto conlleva una serie de castigos.</p>
    <ul>
        <li>Si rompes o dañas un libro: </li>
        <li>Si no devuelves un libro en el plazo establecido: </li>
        <li>Si pintas y/o escribes en un libro: </li>
    </ul>
</section>
</body>
</html>
