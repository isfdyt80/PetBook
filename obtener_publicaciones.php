<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "petbook_user", "P3tB00k_P4ss", "petbook_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener las publicaciones y sus imágenes
$sql = "SELECT 
            p.id, 
            p.descripcion, 
            ap.fecha_ult_vez, 
            ap.valor_recompensa, 
            ap.tel_dueño, 
            p.ubicacion,
            GROUP_CONCAT(i.ruta_imagen) AS imagenes
        FROM publicaciones p
        JOIN animales_perdidos ap ON p.id = ap.publicacion_id
        LEFT JOIN imagenes i ON p.id = i.publicacion_id
        WHERE p.estado = 'perdido'
        GROUP BY p.id
        ORDER BY p.fecha_publicacion DESC";

$result = $conexion->query($sql);

$publicaciones = [];
while ($row = $result->fetch_assoc()) {
    // Convertir la cadena de imágenes en un array
    if (!empty($row['imagenes'])) {
        $row['imagenes'] = explode(",", $row['imagenes']);
    } else {
        $row['imagenes'] = [];
    }
    $publicaciones[] = $row;
}

$conexion->close();
echo json_encode($publicaciones);
