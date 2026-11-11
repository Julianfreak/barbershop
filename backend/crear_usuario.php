<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/conexion.php';

// Solo administradores pueden crear usuarios
requerir_admin();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'JSON no válido.']);
    exit;
}

$nombre = isset($data['user']) ? trim($data['user']) : '';
$username = isset($data['username']) ? trim($data['username']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$roleName = isset($data['role']) ? trim($data['role']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if ($nombre === '' || $username === '' || $roleName === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
    exit;
}

// Validar longitud mínima de contraseña
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

// Validar formato de correo si se proporciona
if ($email !== null && $email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
    exit;
}

// Buscar id del rol por nombre
$rol_stmt = $conn->prepare('SELECT id FROM ROL WHERE nombre = ? LIMIT 1');
$rol_stmt->bind_param('s', $roleName);
$rol_stmt->execute();
$rol_res = $rol_stmt->get_result();
if ($rol_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rol no existe.']);
    exit;
}
$rol_id = (int)$rol_res->fetch_assoc()['id'];
$rol_stmt->close();

// Hash de contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Inserción con prepared statement
$sql = 'INSERT INTO USUARIO (nombre, nombre_usuario, contrasena, rol_id, telefono, correo) VALUES (?, ?, ?, ?, ?, ?)';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Error al preparar INSERT usuario: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    exit;
}

$stmt->bind_param('sssiss', $nombre, $username, $passwordHash, $rol_id, $phone, $email);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.']);
} else {
    // Error de duplicado (nombre_usuario o correo únicos)
    if ($conn->errno === 1062) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario o correo ya existe.']);
    } else {
        error_log('Error al crear usuario: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error al crear el usuario.']);
    }
}

$stmt->close();
$conn->close();
