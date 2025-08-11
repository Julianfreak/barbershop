<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// detectar columnas opcionales
$dbName = $conn->real_escape_string($conn->query("SELECT DATABASE()")->fetch_row()[0]);
function col_exists($conn, $db, $table, $col) {
    $q = $conn->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1");
    $q->bind_param("sss", $db, $table, $col);
    $q->execute();
    $res = $q->get_result()->fetch_row()[0];
    $q->close();
    return ($res > 0);
}
$hasPhone = col_exists($conn, $dbName, 'USUARIO', 'telefono');
$hasEmail = col_exists($conn, $dbName, 'USUARIO', 'correo');

$selectCols = "u.id, u.nombre_usuario, r.nombre AS rol";
if ($hasPhone) $selectCols .= ", u.telefono";
if ($hasEmail) $selectCols .= ", u.correo";

$sql = "SELECT $selectCols FROM USUARIO u JOIN ROL r ON u.rol_id = r.id";
$res = $conn->query($sql);

$usuarios = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        // si no existen columnas, añadir claves vacías para evitar errores en el front
        if (!$hasPhone) $row['telefono'] = '';
        if (!$hasEmail) $row['correo'] = '';
        $usuarios[] = $row;
    }
}

echo json_encode(['success' => true, 'usuarios' => $usuarios]);

$conn->close();
?>
