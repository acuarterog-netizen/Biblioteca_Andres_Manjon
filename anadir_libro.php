<?php

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger y limpiar datos del formulario
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editorial = $_POST['editorial'];
    $isbn = $_POST['isbn'];
    $categoria = $_POST['categoria']; // Esto mapea a 'ubicacion_por_colores'
    
    // Código de barras aleatorio
    $codigo_de_barra = rand(100000, 999999);
    $estado = "Disponible";

    // El orden debe coincidir con la estructura: titulo, codigo_de_barra, autor, editorial, isbn, ubicacion_por_colores, estado_de_actividad
    $sql = "INSERT INTO Libro (titulo, codigo_de_barra, autor, editorial, isbn, ubicacion_por_colores, estado_de_actividad) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    // "sisssss" indica los tipos de datos: s (string), i (integer)
    $stmt->bind_param("sisssss", $titulo, $codigo_de_barra, $autor, $editorial, $isbn, $categoria, $estado);

    // 4. Ejecutar y verificar
    if ($stmt->execute()) {
        echo "<script>
                alert('¡Libro añadido con éxito!');
                window.location.href = 'biblioteca.php'; 
              </script>";
    } else {
        echo "Error al añadir el libro: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>