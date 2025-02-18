<?php
// Información de conexión a la base de datos
$host = 'localhost';
$dbname = 'petbook_db';
$username = 'petbook_user';
$password = 'P3tB00k_P4ss';

// Intento de conexión a la base de datos usando PDO
try {
    // Crear una nueva instancia de PDO con los datos de conexión
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar el modo de error de PDO a excepción, para manejar errores de manera adecuada
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En caso de error, detener la ejecución y mostrar un mensaje de error
    die("Error de conexión: " . $e->getMessage());
}
