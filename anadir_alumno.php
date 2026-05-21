<?php
header('Content-Type: text/html; charset=utf-8');

$host = "localhost";
$usuario = "root";
$clave = "";
$bd = "sistemabiblioteca";
$puerto = 3306;

$conn = new mysqli($host, $usuario, $clave, $bd, $puerto);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $clase = $_POST['clase'];
    $edad = intval($_POST['edad']);
    $username = $_POST['username'];
    $password_raw = $_POST['contrasena'];
    $carnet = $_POST['codigo_de_carnet'];

    // Encriptar contraseña y definir rol de alumno
    $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);
    $id_rol = 3; 
    $estado_sancion = "Sin sanciones";

    $conn->begin_transaction();

    try {
        // Insertar en la tabla Usuario )
        $sql_usuario = "INSERT INTO Usuario (nombre, username, contrasena, codigo_de_carnet, id_rol) VALUES (?, ?, ?, ?, ?)";
        $stmt_u = $conn->prepare($sql_usuario);
        $stmt_u->bind_param("ssssi", $nombre, $username, $password_hashed, $carnet, $id_rol);
        $stmt_u->execute();

        // Insertar en la tabla Alumnado 
        $sql_alumnado = "INSERT INTO Alumnado (nombre, apellidos, clase, edad, codigo_de_carnet, estado_de_sancion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_a = $conn->prepare($sql_alumnado);
        $stmt_a->bind_param("sssiss", $nombre, $apellidos, $clase, $edad, $carnet, $estado_sancion);
        $stmt_a->execute();

        // --- 3. Si todo salió bien, confirmar cambios ---
        $conn->commit();
        
        echo "<script>
                alert('¡Alumno registrado con éxito!');
                window.location.href = 'alumnoslista.php'; 
              </script>";

    } catch (Exception $e) {
        // Si algo falla (ej: carnet duplicado), deshacemos todo
        $conn->rollback();
        echo "Error en el registro: " . $e->getMessage();
    }

    $stmt_u->close();
    $stmt_a->close();
}
$conn->close();
?>