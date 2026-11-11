<?php
session_start();

// Destruir todos los datos de sesión
$_SESSION = [];

// Borrar la cookie de sesión si existe
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Sesión cerrada correctamente.',
], JSON_UNESCAPED_UNICODE);
