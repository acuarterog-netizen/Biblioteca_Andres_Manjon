<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos 'username' y 'contrasena' del formulario HTML
    $user_input = $_POST['username'];
    $pass_input = $_POST['contrasena'];

    // Buscamos en la tabla 'Usuario' y traemos el nombre del Rol
    $sql = "SELECT u.id_usuario, u.contrasena, u.id_rol, r.nombre AS nombre_rol 
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol 
            WHERE u.username = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $user_input);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificamos el hash de la contraseña
        if (password_verify($pass_input, $usuario['contrasena'])) {
            
            // Guardamos los datos necesarios en la SESIÓN
            $_SESSION['usuario_id'] = $usuario['id_usuario']; //
            $_SESSION['id_rol']     = $usuario['id_rol'];     //
            $_SESSION['nombre_rol'] = $usuario['nombre_rol']; //

            // Redirección según el ID del Rol definido en tu base de datos
            switch ($usuario['id_rol']) {
                case 1: // Staff / Bibliotecario
                    header("Location: index.php");
                    break;
                case 2: // Profesor
                    header("Location: index.php");
                
                case 3: // Alumno
                    header("Location: index.php");
                    break;
                default:
                    header("Location: inicio.php");
                    break;
            }
            exit();
        } else {
            header("Location: inicio.php?error=ContrasenaIncorrecta");
        }
    } else {
        header("Location: inicio.php?error=UsuarioNoEncontrado");
    }
    
    $stmt->close();
}
$conexion->close();
?>
