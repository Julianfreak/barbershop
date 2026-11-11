<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

// Solo usuarios autenticados pueden verificar la conexión
requerir_sesion();

echo json_encode([
    'success' => true,
    'message' => 'Conexión exitosa a la base de datos MySQL.',
    'database' => getenv('DB_NAME') ?: 'buzzcut',
    'host' => getenv('DB_HOST') ?: 'mysql',
]);
