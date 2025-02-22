<?php
require '../conexion.php';

$pais_id = intval($_GET['pais_id']);
$sql = "SELECT id, provincia AS nombre FROM provincias WHERE id_pais = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$pais_id]);
$provincias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($provincias);
