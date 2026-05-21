<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="icon" href="images/andresmanjonlogo1.png" type="image/png">
    <link rel="stylesheet" href="styleslogin.css">
</head>
<body>

<div class="imagen-fondo"></div>


<section class="contenedor">
    <img src="images/andresmanjonlogo3.png" alt="Logo del CEIP Andrés Manjón" class="logo-login">

    <form method="POST" action="login.php">
        <h2 class="titulo">Iniciar Sesión</h2><br>

        <section class="formulario">
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" placeholder="Tu usuario" required>
        </section>

        <section class="formulario2">
            <label for="contrasena">Contraseña</label>
            <input type="password" name="contrasena" id="contrasena" placeholder="Tu contraseña" required>
        </section>

        <button type="submit" class="boton"><a href="inicio.php">Iniciar Sesión</a></button>
        <br></br>
        <button type="submit" class="boton"><a href="index.php">Volver a la sesión sin registro</a></button>

    </form>
</section>
</body>
<?php
    // Si la URL contiene el error específico, mostramos el HTML
    if (isset($_GET['error']) && $_GET['error'] === 'UsuarioNoEncontrado'):
    ?>
        <div class="overlay">
            <div class="popup">
                <h2>¡Error!</h2>
                <p>Este usuario no está registrado en la base de datos.</p>
                
                <a href="inicio.php" class="btn-cerrar">Cerrar</a>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Si la URL contiene el error específico, mostramos el HTML
    if (isset($_GET['error']) && $_GET['error'] === 'ContrasenaIncorrecta'):
    ?>
        <div class="overlay">
            <div class="popup">
                <h2>¡Error!</h2>
                <p>Tu contraseña o usuario es incorrecto. Por favor, intentelo de nuevo.</p>
                
                <a href="inicio.php" class="btn-cerrar">Cerrar</a>
            </div>
        </div>
    <?php endif; ?>

</html>