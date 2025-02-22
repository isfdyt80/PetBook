<?php
require '../conexion.php';

$sql = "SELECT id, nombre FROM especies";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$paises = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($paises);
