<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// No filtrar errores PHP al cliente en producción
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/conexion.php';

$input = file_get_contents('php://input');
$datos = json_decode($input, true);

if (!isset($datos['username'], $datos['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o JSON inválido.']);
    exit;
}

$username = trim($datos['username']);
$password = $datos['password'];

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña vacíos.']);
    exit;
}

$sql = "SELECT id, nombre_usuario, contrasena, rol_id
        FROM USUARIO
        WHERE nombre_usuario = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Error al preparar consulta de login: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$resultado = $stmt->get_result();

// Mensaje genérico para evitar enumeración de usuarios
$mensajeError = 'Usuario o contraseña incorrectos.';

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
    $hashAlmacenado = $usuario['contrasena'];

    // Solo se aceptan contraseñas hasheadas con password_hash()
    if (password_verify($password, $hashAlmacenado)) {
        // Regenerar el ID de sesión para prevenir fijación de sesión
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'rol_id' => $usuario['rol_id'],
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'id' => $usuario['id'],
            'username' => $usuario['nombre_usuario'],
            'rol_id' => $usuario['rol_id'],
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $mensajeError]);
    }
} else {
    echo json_encode(['success' => false, 'message' => $mensajeError]);
}

$stmt->close();
$conn->close();
