<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "petbook_user", "P3tB00k_P4ss", "petbook_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

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
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        $nombre_foto = basename($_FILES['fotos']['name'][$key]);
        $ruta_destino = "uploads/" . $nombre_foto;
        if (move_uploaded_file($tmp_name, $ruta_destino)) {
            $fotos[] = $ruta_destino;
        }
    }
}

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

$conexion->close();
echo "Publicación guardada exitosamente.";
