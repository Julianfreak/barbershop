<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// leer JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'JSON no válido.']);
    exit;
}

$nombre = isset($data['user']) ? trim($data['user']) : '';
$username = isset($data['username']) ? trim($data['username']) : '';
$phone    = isset($data['phone']) ? trim($data['phone']) : null;
$email    = isset($data['email']) ? trim($data['email']) : null;
$roleName = isset($data['role']) ? trim($data['role']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if ($nombre === '' || $username === '' || $roleName === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
    exit;
}

// buscar id del rol por nombre
$rol_stmt = $conn->prepare("SELECT id FROM ROL WHERE nombre = ? LIMIT 1");
$rol_stmt->bind_param('s', $roleName);
$rol_stmt->execute();
$rol_res = $rol_stmt->get_result();
if ($rol_res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Rol no existe.']);
    exit;
}
$rol_id = (int)$rol_res->fetch_assoc()['id'];
$rol_stmt->close();

// detectar si las columnas telefono y correo existen en USUARIO
$dbName = $conn->real_escape_string($conn->query("SELECT DATABASE()")->fetch_row()[0]);
function column_exists($conn, $dbName, $table, $column) {
    $q = $conn->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1");
    $q->bind_param("sss", $dbName, $table, $column);
    $q->execute();
    $r = $q->get_result()->fetch_row()[0];
    $q->close();
    return ($r > 0);
}
$hasPhone = column_exists($conn, $dbName, 'USUARIO', 'telefono');
$hasEmail = column_exists($conn, $dbName, 'USUARIO', 'correo');

// preparar inserción dinámica
$cols = ['nombre','nombre_usuario','contrasena','rol_id'];
$placeholders = ['?','?','?','?'];
$params = [];
$types = 'sssi';

// hash de contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// valores base
$params[] = $nombre;
$params[] = $username;
$params[] = $passwordHash;
$params[] = $rol_id;

// agregar opcionales si existen
if ($hasPhone && $phone !== null) {
    $cols[] = 'telefono';
    $placeholders[] = '?';
    $types .= 's';
    $params[] = $phone;
}
if ($hasEmail && $email !== null) {
    $cols[] = 'correo';
    $placeholders[] = '?';
    $types .= 's';
    $params[] = $email;
}

$sql = "INSERT INTO USUARIO (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
    exit;
}

// bind dinámico (bind_param necesita referencias)
$bind_names[] = $types;
for ($i=0; $i<count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
