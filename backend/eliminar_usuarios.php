<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

// Solo administradores pueden eliminar usuarios
$usuarioSesion = requerir_admin();

// Leer ID (acepta JSON o form-encoded para compatibilidad con el frontend)
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id_usuario = null;

if ($data && isset($data['id'])) {
    $id_usuario = intval($data['id']);
} elseif (isset($_POST['id'])) {
    $id_usuario = intval($_POST['id']);
}

if (!$id_usuario) {
    echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID de usuario válido.']);
    exit;
}

// Evitar que un usuario se elimine a sí mismo
if ($id_usuario === (int)$usuarioSesion['id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM USUARIO WHERE id = ?');
if (!$stmt) {
    error_log('Error al preparar DELETE usuario: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    exit;
}

$stmt->bind_param('i', $id_usuario);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'El usuario no existe.']);
    }
} else {
    error_log('Error al eliminar usuario: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario.']);
}

$stmt->close();
$conn->close();
