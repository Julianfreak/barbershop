<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

// Solo administradores pueden ver la lista de usuarios
requerir_admin();

$sql = 'SELECT u.id, u.nombre, u.nombre_usuario, r.nombre AS rol, u.telefono, u.correo
        FROM USUARIO u
        JOIN ROL r ON u.rol_id = r.id
        ORDER BY u.id ASC';

$res = $conn->query($sql);

$usuarios = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode(['success' => true, 'usuarios' => $usuarios], JSON_UNESCAPED_UNICODE);
$conn->close();
