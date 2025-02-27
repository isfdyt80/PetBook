<?php
require '../conexion.php'; // Incluye el archivo de conexión a la base de datos

// Consulta SQL para obtener las especies
$sql = "SELECT id, nombre FROM especies";
$stmt = $pdo->prepare($sql); // Prepara la consulta
$stmt->execute(); // Ejecuta la consulta
$paises = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtiene todos los resultados como un array asociativo

echo json_encode($paises); // Devuelve los resultados en formato JSON
