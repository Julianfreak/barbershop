<?php
/**
 * Ejemplo de archivo de conexión a MySQL.
 * Copia este archivo como backend/conexion.php y no lo subas a Git:
 *   cp backend/conexion.example.php backend/conexion.php
 *
 * Los valores se inyectan como variables de entorno desde Docker Compose (.env).
 * Si ejecutas sin Docker, reemplaza los getenv() por tus valores reales.
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('DB_HOST') ?: 'mysql';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'buzzcut';
$port = (int)(getenv('DB_PORT') ?: 3306);

try {
    $conn = new mysqli($host, $user, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log('Error de conexión a MySQL: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'No fue posible conectar con la base de datos.',
    ]);
    exit;
}
