<?php
require '../conexion.php'; // Incluye el archivo de conexión a la base de datos

$pais_id = intval($_GET['pais_id']); // Obtiene el ID del país desde los parámetros de la URL y lo convierte a un entero
$sql = "SELECT id, provincia AS nombre FROM provincias WHERE id_pais = ?"; // Consulta SQL para obtener las provincias de un país específico
$stmt = $pdo->prepare($sql); // Prepara la consulta
$stmt->execute([$pais_id]); // Ejecuta la consulta con el ID del país como parámetro
$provincias = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como un array asociativo

echo json_encode($provincias); // Devuelve los resultados en formato JSON
