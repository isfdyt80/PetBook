<?php
header("Content-Type: application/json");
include '../conexion.php';

try {

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

    $stmt = $pdo->query($sql);
    $publicaciones = $stmt->fetchAll();

    echo json_encode($publicaciones);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
}
