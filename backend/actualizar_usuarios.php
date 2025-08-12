<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'JSON inválido o falta id']);
    exit;
}
$id = intval($data['id']);
$nombre = isset($data['user']) ? trim($data['user']) : null;
$username = isset($data['username']) ? trim($data['username']) : null;
$phone = isset($data['phone']) ? trim($data['phone']) : null;
$email = isset($data['email']) ? trim($data['email']) : null;
$roleName = isset($data['role']) ? trim($data['role']) : null;
$password = isset($data['password']) ? $data['password'] : null;

// Obtener rol_id si se pasó rol por nombre
$rol_id = null;
if ($roleName !== null) {
    $r = $conn->prepare("SELECT id FROM ROL WHERE nombre = ? LIMIT 1");
    $r->bind_param("s", $roleName);
    $r->execute();
    $res = $r->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Rol no existe']);
        exit;
    }
    $rol_id = (int)$res->fetch_assoc()['id'];
    $r->close();
}

// Construir UPDATE dinámico
$sets = [];
$params = [];
$types = '';

if ($nombre !== null) { $sets[] = "nombre = ?"; $params[] = $nombre; $types .= 's'; }
if ($username !== null) { $sets[] = "nombre_usuario = ?"; $params[] = $username; $types .= 's'; }
if ($password !== null && $password !== '') { // actualizar solo si no está vacío
    $sets[] = "contrasena = ?";
    $params[] = password_hash($password, PASSWORD_DEFAULT);
    $types .= 's';
}
if ($rol_id !== null) { $sets[] = "rol_id = ?"; $params[] = $rol_id; $types .= 'i'; }
if ($phone !== null) { $sets[] = "telefono = ?"; $params[] = $phone; $types .= 's'; }
if ($email !== null) { $sets[] = "correo = ?"; $params[] = $email; $types .= 's'; }

if (count($sets) === 0) {
    echo json_encode(['success' => false, 'message' => 'Nada para actualizar']);
    exit;
}

$sql = "UPDATE USUARIO SET " . implode(', ', $sets) . " WHERE id = ?";
$params[] = $id;
$types .= 'i';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparación: '.$conn->error]);
    exit;
}

// bind dinámico
$bind_names[] = $types;
for ($i=0;$i<count($params);$i++){
    $bind_name = 'bind'.$i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: '.$stmt->error]);
}
$stmt->close();
$conn->close();
?>
