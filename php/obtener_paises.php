<?php
require '../conexion.php'; // Incluye el archivo de conexión a la base de datos

$sql = "SELECT id, nombre FROM paises"; // Consulta SQL para obtener los países
$stmt = $pdo->prepare($sql); // Prepara la consulta
$stmt->execute(); // Ejecuta la consulta
$paises = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como un array asociativo

echo json_encode($paises); // Devuelve los resultados en formato JSON
