<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

// Solo administradores pueden actualizar usuarios
requerir_admin();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'JSON inválido o falta id.']);
    exit;
}

$id = intval($data['id']);
$nombre = isset($data['user']) ? trim($data['user']) : null;
$username = isset($data['username']) ? trim($data['username']) : null;
$phone = isset($data['phone']) ? trim($data['phone']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$roleName = isset($data['role']) ? trim($data['role']) : null;
$password = isset($data['password']) ? $data['password'] : null;

// Validar formato de correo si se proporciona
if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
    exit;
}

// Obtener rol_id si se pasó rol por nombre
$rol_id = null;
if ($roleName !== null) {
    $r = $conn->prepare('SELECT id FROM ROL WHERE nombre = ? LIMIT 1');
    $r->bind_param('s', $roleName);
    $r->execute();
    $res = $r->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Rol no existe.']);
        exit;
    }
    $rol_id = (int)$res->fetch_assoc()['id'];
    $r->close();
}

// Construir UPDATE dinámico
$sets = [];
$params = [];
$types = '';

if ($nombre !== null) { $sets[] = 'nombre = ?'; $params[] = $nombre; $types .= 's'; }
if ($username !== null) { $sets[] = 'nombre_usuario = ?'; $params[] = $username; $types .= 's'; }
if ($password !== null && $password !== '') {
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit;
    }
    $sets[] = 'contrasena = ?';
    $params[] = password_hash($password, PASSWORD_DEFAULT);
    $types .= 's';
}
if ($rol_id !== null) { $sets[] = 'rol_id = ?'; $params[] = $rol_id; $types .= 'i'; }
if ($phone !== null) { $sets[] = 'telefono = ?'; $params[] = $phone; $types .= 's'; }
if ($email !== null) { $sets[] = 'correo = ?'; $params[] = $email; $types .= 's'; }

if (count($sets) === 0) {
    echo json_encode(['success' => false, 'message' => 'Nada para actualizar.']);
    exit;
}

$sql = 'UPDATE USUARIO SET ' . implode(', ', $sets) . ' WHERE id = ?';
$params[] = $id;
$types .= 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Error al preparar UPDATE usuario: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    exit;
}

// Bind dinámico
$bind_names[] = $types;
for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
} else {
    if ($conn->errno === 1062) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario o correo ya existe.']);
    } else {
        error_log('Error al actualizar usuario: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
    }
}

$stmt->close();
$conn->close();
