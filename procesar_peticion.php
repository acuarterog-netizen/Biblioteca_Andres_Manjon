<?php
session_start();
$conn = new mysqli("localhost", "root", "", "", 3306);

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

// 1. Validar datos
if (empty($_POST['id_libro'])) { header("Location: peticion.php?error=no_libro"); exit; }
if (empty($_POST['alumno_info'])) { header("Location: peticion.php?error=no_alumno"); exit; }

// 2. Extraer ID Alumno y el Código de Carnet
$datos = explode('|', $_POST['alumno_info']);
$id_alumnado = $datos[0];
$codigo_carnet = $datos[1];
$id_libro = $_POST['id_libro'];

// 3. BUSCAR EL ID_USUARIO REAL BASADO EN EL CARNET
$sql_user = "SELECT id_usuario FROM usuario WHERE codigo_de_carnet = ?";
$stmt_u = $conn->prepare($sql_user);
$stmt_u->bind_param("s", $codigo_carnet);
$stmt_u->execute();
$res_u = $stmt_u->get_result();
$usuario_encontrado = $res_u->fetch_assoc();

if (!$usuario_encontrado) {
    header("Location: peticion.php?error=no_usuario");
    exit;
}
$id_usuario_real = $usuario_encontrado['id_usuario']; // Aquí estará el 8 para Mateo

// 4. Fechas (Hoy y +1 mes)
$fecha_salida = date('Y-m-d H:i:s');
$fecha_devolucion = date('Y-m-d H:i:s', strtotime('+1 month'));

// 5. INSERTAR PRÉSTAMO CON ID_USUARIO CORRECTO
$sql_ins = "INSERT INTO prestamo (id_alumnado, id_libro, id_usuario, fecha_de_salida, fecha_de_devolucion, estado_del_prestamo) 
            VALUES (?, ?, ?, ?, ?, 'Activo')";
$stmt_i = $conn->prepare($sql_ins);
$stmt_i->bind_param("iiiss", $id_alumnado, $id_libro, $id_usuario_real, $fecha_salida, $fecha_devolucion);

if ($stmt_i->execute()) {
    $conn->query("UPDATE libro SET estado_de_actividad = 'No Disponible' WHERE id_libro = $id_libro");
    header("Location: perfil.php?mensaje=PrestamoExitoso");
} else {
    echo "Error: " . $conn->error;
}
?>