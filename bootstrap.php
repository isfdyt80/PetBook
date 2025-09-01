<?php
// bootstrap.php
require_once __DIR__ . '/core/Env.php';
require_once __DIR__ . '/database/Conexion.php';
use Core\Env;

// Cargar variables de entorno
Env::load(__DIR__ . '/.env');
