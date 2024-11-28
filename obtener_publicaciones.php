<?php
$conexion = new mysqli("localhost", "petbook_user", "P3tB00k_P4ss", "petbook_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT p.id, p.descripcion, ap.fecha_ult_vez, ap.valor_recompensa, ap.tel_dueño, p.ubicacion 
        FROM publicaciones p
        JOIN animales_perdidos ap ON p.id = ap.publicacion_id
        WHERE p.estado = 'perdido'
        ORDER BY p.fecha_publicacion DESC";
$result = $conexion->query($sql);

$publicaciones = [];
while ($row = $result->fetch_assoc()) {
    $publicaciones[] = $row;
}

$conexion->close();
echo json_encode($publicaciones);
