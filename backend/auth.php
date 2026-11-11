<?php
/**
 * Helper de autenticación y autorización.
 * Incluir este archivo al inicio de cada endpoint que requiera sesión.
 *
 * Uso:
 *   require_once __DIR__ . '/auth.php';
 *   $usuario = requerir_sesion();     // cualquier usuario autenticado
 *   $usuario = requerir_admin();      // solo rol admin (rol_id = 1)
 */

/**
 * Inicia la sesión si no está activa y verifica que el usuario esté autenticado.
 * Si no lo está, responde 401 y termina la ejecución.
 *
 * @return array Datos del usuario en sesión (id, nombre_usuario, rol_id)
 */
function requerir_sesion(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['usuario']['id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Debes iniciar sesión para acceder a este recurso.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $_SESSION['usuario'];
}

/**
 * Verifica que el usuario autenticado tenga rol de administrador (rol_id = 1).
 * Si no lo es, responde 403 y termina la ejecución.
 *
 * @return array Datos del usuario en sesión
 */
function requerir_admin(): array
{
    $usuario = requerir_sesion();

    if ((int)$usuario['rol_id'] !== 1) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $usuario;
}
