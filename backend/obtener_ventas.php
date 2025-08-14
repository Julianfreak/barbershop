<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$dbName = $conn->real_escape_string($conn->query("SELECT DATABASE()")->fetch_row()[0]);
function col_exists($conn, $db, $table, $col) {
    $q = $conn->prepare("SELECT COUNT(*) FROM information_schema.columns 
                         WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1");
    $q->bind_param("sss", $db, $table, $col);
    $q->execute();
    $r = $q->get_result()->fetch_row()[0];
    $q->close();
    return ($r > 0);
}

$hasFecha = col_exists($conn, $dbName, 'VENTA', 'fecha');
$hasCreatedAt = col_exists($conn, $dbName, 'VENTA', 'created_at');

$dateCol = $hasFecha ? 'v.fecha' : ($hasCreatedAt ? 'v.created_at' : 'NULL');

$sql = "SELECT v.id, u.nombre_usuario AS empleado, v.servicio, v.productos, v.total, $dateCol AS fecha
        FROM VENTA v
        JOIN USUARIO u ON v.usuario_id = u.id
        ORDER BY v.id DESC";

$res = $conn->query($sql);
$ventas = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $ventas[] = $row;
    }
}

echo json_encode(['success' => true, 'ventas' => $ventas]);
$conn->close();
