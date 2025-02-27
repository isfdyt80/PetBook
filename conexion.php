<?php
$host = 'localhost'; // Dirección del servidor de la base de datos
$dbname = 'petbook_db'; // Nombre de la base de datos
$username = 'petbook_user'; // Nombre de usuario de la base de datos
$password = 'P3tB00k_P4ss'; // Contraseña del usuario de la base de datos

try {
    // Crea una nueva conexión PDO a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Establece el modo de error de PDO a excepción
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Maneja cualquier error de conexión
    die("Error de conexión: " . $e->getMessage());
}
