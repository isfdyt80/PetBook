<?php
require '../conexion.php'; // Archivo con la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {

        // Sanitización de los datos
        $tipo_publicacion = trim($_POST['tipo_publicacion']);
        $descripcion = trim($_POST['descripcion']);
        $telefono = trim($_POST['telefono']);
        $pais = intval($_POST['pais']);
        $provincia = intval($_POST['provincia']);
        $ciudad = intval($_POST['ciudad']);
        $especie = intval($_POST['especie']);
        $falta_desde = !empty($_POST['falta_desde']) ? $_POST['falta_desde'] : NULL;
        $encontrado_el = !empty($_POST['encontrado_el']) ? $_POST['encontrado_el'] : NULL;
        $valor_recompensa = isset($_POST['valor_recompensa']) ? intval($_POST['valor_recompensa']) : NULL;

        if (empty($tipo_publicacion) || empty($descripcion) || empty($telefono) || empty($pais) || empty($provincia) || empty($ciudad) || empty($especie)) {
            echo json_encode(["success" => false, "message" => "Todos los campos obligatorios deben completarse."]);
            exit();
        }

        // Manejo de la imagen
        $foto = NULL;
        if (!empty($_FILES['foto']['name'])) {
            $ruta_destino = "uploads/";
            $nombre_archivo = time() . "_" . basename($_FILES["foto"]["name"]);
            $ruta_completa = $ruta_destino . $nombre_archivo;

            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_completa)) {
                $foto = $ruta_completa;
            } else {
                echo json_encode(["success" => false, "message" => "Error al subir la imagen."]);
                exit();
            }
        }

        // Inserción de datos en la tabla publicaciones
        $sql = "INSERT INTO publicaciones (fecha_publicacion, tipo_publicacion, foto, especie, descripcion, telefono, pais, provincia, ciudad, falta_desde, encontrado_el, valor_recompensa) 
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tipo_publicacion, $foto, $especie, $descripcion, $telefono, $pais, $provincia, $ciudad, $falta_desde, $encontrado_el, $valor_recompensa]);

        echo json_encode(["success" => true, "message" => "Publicación guardada exitosamente."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Acceso denegado."]);
}
