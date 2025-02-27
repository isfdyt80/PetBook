<?php
header("Content-Type: application/json"); // Establece el tipo de contenido de la respuesta como JSON
include '../conexion.php'; // Incluye el archivo de conexión a la base de datos

try {
    // Consulta SQL para obtener las publicaciones
    $sql = "SELECT 
                p.id,
                p.fecha_publicacion,
                p.tipo_publicacion,
                p.foto AS foto_perfil,
                e.nombre AS especie,
                p.descripcion,
                p.telefono,
                pa.nombre AS pais,
                pr.provincia AS provincia,
                ci.localidad AS ciudad,
                p.falta_desde,
                p.encontrado_el,
                p.valor_recompensa
            FROM publicaciones p
            JOIN especies e ON p.especie = e.id
            JOIN paises pa ON p.pais = pa.id
            JOIN provincias pr ON p.provincia = pr.id
            JOIN localidades ci ON p.ciudad = ci.id
            ORDER BY p.fecha_publicacion DESC";

    $stmt = $pdo->query($sql); // Ejecuta la consulta
    $publicaciones = $stmt->fetchAll(); // Obtiene todos los resultados

    echo json_encode($publicaciones); // Devuelve los resultados en formato JSON
} catch (PDOException $e) {
    // Maneja cualquier error de conexión
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
}
