<?php
require '../conexion.php'; // Archivo con la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (isset($_POST['tipo_publicacion_perdido'])) {
            // Sanitización de los datos
            $tipo_publicacion = trim($_POST['tipo_publicacion_perdido']);
            $descripcion = trim($_POST['descripcion_perdido']);
            $telefono = trim($_POST['telefono_perdido']);
            $pais = intval($_POST['pais_perdido']);
            $provincia = intval($_POST['provincia_perdido']);
            $ciudad = intval($_POST['ciudad_perdido']);
            $especie = intval($_POST['especie_perdido']);
            $falta_desde = !empty($_POST['falta_desde_perdido']) ? $_POST['falta_desde_perdido'] : " ";
            $encontrado_el = !empty($_POST['encontrado_el_perdido']) ? $_POST['encontrado_el_perdido'] : " ";
            $valor_recompensa = isset($_POST['valor_recompensa_perdido']) ? intval($_POST['valor_recompensa_perdido']) : " ";
        } elseif (isset($_POST['tipo_publicacion_encontrado'])) {
            // Sanitización de los datos
            $tipo_publicacion = trim($_POST['tipo_publicacion_encontrado']);
            $descripcion = trim($_POST['descripcion_encontrado']);
            $telefono = trim($_POST['telefono_encontrado']);
            $pais = intval($_POST['pais_encontrado']);
            $provincia = intval($_POST['provincia_encontrado']);
            $ciudad = intval($_POST['ciudad_encontrado']);
            $especie = intval($_POST['especie_encontrado']);
            $falta_desde = !empty($_POST['falta_desde_encontrado']) ? $_POST['falta_desde_encontrado'] : " ";
            $encontrado_el = !empty($_POST['encontrado_el_encontrado']) ? $_POST['encontrado_el_encontrado'] : " ";
            $valor_recompensa = isset($_POST['valor_recompensa_encontrado']) ? intval($_POST['valor_recompensa_encontrado']) : " ";
        } elseif (isset($_POST['tipo_publicacion_adopcion'])) {
            // Sanitización de los datos
            $tipo_publicacion = trim($_POST['tipo_publicacion_adopcion']);
            $descripcion = trim($_POST['descripcion_adopcion']);
            $telefono = !empty($_POST['telefono_adopcion']) ? trim($_POST['telefono_adopcion']) : " ";
            $pais = intval($_POST['pais_adopcion']);
            $provincia = intval($_POST['provincia_adopcion']);
            $ciudad = intval($_POST['ciudad_adopcion']);
            $especie = intval($_POST['especie_adopcion']);
            $falta_desde = !empty($_POST['falta_desde_adopcion']) ? $_POST['falta_desde_adopcion'] : " ";
            $encontrado_el = !empty($_POST['encontrado_el_adopcion']) ? $_POST['encontrado_el_adopcion'] : " ";
            $valor_recompensa = isset($_POST['valor_recompensa_adopcion']) ? intval($_POST['valor_recompensa_adopcion']) : " ";
        }


        if (empty($tipo_publicacion) || empty($descripcion) || empty($telefono) || empty($pais) || empty($provincia) || empty($ciudad) || empty($especie)) {
            echo json_encode(["success" => false, "message" => "Todos los campos obligatorios deben completarse."]);
            exit();
        }

        // Manejo de la imagen
        $foto = NULL;
        if (!empty($_FILES['foto_perdido']['name'])) {
            $ruta_destino = "uploads/";
            $nombre_archivo = time() . "_" . basename($_FILES["foto_perdido"]["name"]);
            $ruta_completa = $ruta_destino . $nombre_archivo;

            if (move_uploaded_file($_FILES["foto_perdido"]["tmp_name"], $ruta_completa)) {
                $foto = $ruta_completa;
            } else {
                echo json_encode(["success" => false, "message" => "Error al subir la imagen."]);
                exit();
            }
        } elseif (!empty($_FILES['foto_encontrado']['name'])) {
            $ruta_destino = "uploads/";
            $nombre_archivo = time() . "_" . basename($_FILES["foto_encontrado"]["name"]);
            $ruta_completa = $ruta_destino . $nombre_archivo;

            if (move_uploaded_file($_FILES["foto_encontrado"]["tmp_name"], $ruta_completa)) {
                $foto = $ruta_completa;
            } else {
                echo json_encode(["success" => false, "message" => "Error al subir la imagen."]);
                exit();
            }
        } elseif (!empty($_FILES['foto_adopcion']['name'])) {
            $ruta_destino = "uploads/";
            $nombre_archivo = time() . "_" . basename($_FILES["foto_adopcion"]["name"]);
            $ruta_completa = $ruta_destino . $nombre_archivo;

            if (move_uploaded_file($_FILES["foto_adopcion"]["tmp_name"], $ruta_completa)) {
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
