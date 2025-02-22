<?php
require '../conexion.php';

$provincia_id = intval($_GET['provincia_id']);
$sql = "SELECT id, localidad AS nombre FROM localidades WHERE id_provincia = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$provincia_id]);
$localidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($localidades);
