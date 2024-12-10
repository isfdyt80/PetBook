<?php
$host = 'localhost';
$dbname = 'petbook_db';
$username = 'petbook_user';
$password = 'P3tB00k_P4ss';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
