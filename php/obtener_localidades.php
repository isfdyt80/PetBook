<?php
require '../conexion.php'; // Incluye el archivo de conexión a la base de datos

$provincia_id = intval($_GET['provincia_id']); // Obtiene el ID de la provincia desde los parámetros de la URL y lo convierte a un entero
$sql = "SELECT id, localidad AS nombre FROM localidades WHERE id_provincia = ?"; // Consulta SQL para obtener las localidades de una provincia específica
$stmt = $pdo->prepare($sql); // Prepara la consulta
$stmt->execute([$provincia_id]); // Ejecuta la consulta con el ID de la provincia como parámetro
$localidades = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como un array asociativo

echo json_encode($localidades); // Devuelve los resultados en formato JSON
