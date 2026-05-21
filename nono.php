<div class="contenedor-grid">
<?php
if ($resultado && $resultado->num_rows > 0):
    while($item = $resultado->fetch_assoc()): 
        
        $id_actual = $item['id'] 
            ?? $item['id_libro'] 
            ?? $item['id_libros'] 
            ?? $item['libro_id'] 
            ?? 0;

        $estado = strtolower(trim($item['estado_de_actividad']));
        $esDisponible = strpos($estado, 'disponible') !== false;
        $colorEstado = $esDisponible ? '#28a745' : '#dc3545';

        $categoria = trim($item['ubicacion_por_colores']);
        $categoriaKey = strtolower($categoria);
        
        $catToImg = [
            "negro"  => "negro.png",
            "rojo"   => "rojo.png",
            "marron" => "marron.png",
            "marrón" => "marron.png"
        ];
        
        $imgCategoria = $catToImg[$categoriaKey] ?? "default.png";
        
        // -------------------------------
        // IMAGEN DEL LIBRO
        // -------------------------------
        // Verifica qué nombre de columna usas para la imagen
        $imagenLibro = $item['imagen'] 
            ?? $item['portada'] 
            ?? $item['foto'] 
            ?? $item['imagen_url'] 
            ?? 'default-book.jpg'; // Imagen por defecto
        
        // Si la imagen está vacía o no existe, usa una por defecto
        if (empty($imagenLibro) || $imagenLibro == '') {
            $imagenLibro = 'default-book.jpg';
        }
?>
    
    <div class="libro-card">

        <!-- CABECERA CON CATEGORÍA -->
        <div class="card-header-cat">
            <img src="img/categorias/<?php echo $imgCategoria; ?>" class="cat-icon">
            <span class="categoria-texto"><?php echo htmlspecialchars($categoria); ?></span>
        </div>

        <!-- IMAGEN DEL LIBRO -->
        <div class="card-imagen">
            <img src="img/libros/<?php echo htmlspecialchars($imagenLibro); ?>" 
                 alt="Portada de <?php echo htmlspecialchars($item['titulo']); ?>"
                 class="portada-libro">
        </div>

        <!-- CUERPO CON INFORMACIÓN -->
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

        <!-- PIE DE TARJETA -->
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