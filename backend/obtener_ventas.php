<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

if (empty($_SESSION['usuario']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para consultar las ventas.']);
    exit;
}

$sql = 'SELECT v.id, u.nombre_usuario AS empleado, v.servicio, v.productos, v.total, v.fecha
        FROM VENTA v
        INNER JOIN USUARIO u ON v.usuario_id = u.id
        ORDER BY v.id DESC';

$resultado = $conn->query($sql);
$ventas = [];
while ($fila = $resultado->fetch_assoc()) {
    $ventas[] = $fila;
}

echo json_encode(['success' => true, 'ventas' => $ventas], JSON_UNESCAPED_UNICODE);
$conn->close();