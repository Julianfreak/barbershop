<?php
/*
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';

$input = file_get_contents("php://input");
$datos = json_decode($input, true);

if (!isset($datos['username'], $datos['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o JSON inválido.']);
    exit;
}

$username = trim($datos['username']);
$password = trim($datos['password']);

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña vacíos.']);
    exit;
}

// Buscar solo por nombre de usuario
$sql = "SELECT USUARIO.id, USUARIO.nombre_usuario, USUARIO.contrasena, ROL.nombre AS rol
        FROM USUARIO
        JOIN ROL ON USUARIO.rol_id = ROL.id
        WHERE USUARIO.nombre_usuario = ?
        LIMIT 1";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        $hashAlmacenado = $usuario['contrasena'];

        // Verificar si es un hash bcrypt
        if (password_verify($password, $hashAlmacenado) || $password === $hashAlmacenado) {
            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'role' => $usuario['rol'],
                'username' => $usuario['nombre_usuario'],
                'user_id' => $usuario['id']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
}

$conn->close();
*/
session_start(); // <- Siempre al inicio antes de cualquier echo o header
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';

$input = file_get_contents("php://input");
$datos = json_decode($input, true);

if (!isset($datos['username'], $datos['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o JSON inválido.']);
    exit;
}

$username = trim($datos['username']);
$password = trim($datos['password']);

if ($username === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Usuario o contraseña vacíos.']);
    exit;
}

$sql = "SELECT id, nombre_usuario, contrasena, rol_id
        FROM USUARIO
        WHERE nombre_usuario = ?
        LIMIT 1";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $hashAlmacenado = $usuario['contrasena'];

        if (password_verify($password, $hashAlmacenado) || $password === $hashAlmacenado) {
            
            // Guardar en sesión
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre_usuario' => $usuario['nombre_usuario'],
                'rol_id' => $usuario['rol_id']
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'id' => $usuario['id'],
                'username' => $usuario['nombre_usuario'],
                'rol_id' => $usuario['rol_id']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Credenciales inválidas.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
}

$conn->close();

?>