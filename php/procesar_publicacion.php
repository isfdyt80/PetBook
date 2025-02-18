<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

// Capturar datos del formulario
$usuario_id = 1; // Simula un usuario autenticado
$caracteristicas = $_POST['caracteristicas'];
$falta_desde = $_POST['faltaDesde'];
$ubicacion = $_POST['ubicacion'];
$telefono = $_POST['telefono'];
$recompensa = isset($_POST['recompensaCheckbox']) ? 1 : 0;
$valor_recompensa = $recompensa ? $_POST['valorRecompensa'] : null;

// Manejar la carga de fotos
$fotos = [];
if (isset($_FILES['fotos'])) {
    $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . "/img_publicaciones/";
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true); // Crear la carpeta si no existe
    }

    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        $nombre_foto = uniqid() . "_" . basename($_FILES['fotos']['name'][$key]);
        $ruta_destino = $directorio_destino . $nombre_foto;
        if (move_uploaded_file($tmp_name, $ruta_destino)) {
            $fotos[] = $ruta_destino;
        }
    }
}

echo $fotos;

// Insertar datos en la tabla publicaciones
$sql = "INSERT INTO publicaciones (usuario_id, estado, descripcion, ubicacion, nombre_mascota, fecha_publicacion)
        VALUES (?, 'perdido', ?, ?, NULL, NOW())";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iss", $usuario_id, $caracteristicas, $ubicacion);
$stmt->execute();
$publicacion_id = $stmt->insert_id; // Obtener el ID de la publicación recién creada

// Insertar datos específicos en animales_perdidos
$sql_perdido = "INSERT INTO animales_perdidos (publicacion_id, fecha_ult_vez, valor_recompensa, tel_dueño)
                VALUES (?, ?, ?, ?)";
$stmt_perdido = $conexion->prepare($sql_perdido);
$stmt_perdido->bind_param("isss", $publicacion_id, $falta_desde, $valor_recompensa, $telefono);
$stmt_perdido->execute();

// Insertar las rutas de las imágenes en la tabla imagenes
if (!empty($fotos)) {
    $sql_imagen = "INSERT INTO imagenes (publicacion_id, ruta_imagen) VALUES (?, ?)";
    $stmt_imagen = $conexion->prepare($sql_imagen);
    foreach ($fotos as $ruta) {
        $stmt_imagen->bind_param("is", $publicacion_id, $ruta);
        $stmt_imagen->execute();
    }
}

$conexion->close();
//echo "Publicación guardada exitosamente.";
