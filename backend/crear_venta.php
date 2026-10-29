<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

function responder(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

// El ID del usuario debe provenir de la sesión del servidor, no del navegador.
$usuarioId = $_SESSION['usuario']['id'] ?? null;
if (!$usuarioId) {
    responder(401, ['success' => false, 'message' => 'Debes iniciar sesión para registrar una venta.']);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    responder(400, ['success' => false, 'message' => 'El cuerpo de la solicitud no contiene JSON válido.']);
}

$servicio = trim((string)($data['servicio'] ?? ''));
$productos = $data['productos'] ?? [];
if (!is_array($productos)) {
    responder(400, ['success' => false, 'message' => 'La lista de productos no es válida.']);
}

$preciosServicios = [
    'corte_basico' => 10000.00,
    'corte_avanzado' => 15000.00,
    'corte_barba' => 18000.00,
    'barba' => 8000.00,
];

if ($servicio !== '' && !array_key_exists($servicio, $preciosServicios)) {
    responder(400, ['success' => false, 'message' => 'El servicio seleccionado no es válido.']);
}

$productos = array_values(array_unique(array_filter($productos, static function ($codigo) {
    return is_string($codigo) && $codigo !== '';
})));

if ($servicio === '' && count($productos) === 0) {
    responder(400, ['success' => false, 'message' => 'Selecciona un servicio o al menos un producto.']);
}

try {
    $conn->begin_transaction();
    $total = $servicio !== '' ? $preciosServicios[$servicio] : 0.00;
    $productosGuardados = [];

    if (count($productos) > 0) {
        $stmtProducto = $conn->prepare(
            'SELECT id, codigo, nombre, precio FROM PRODUCTO WHERE activo = 1 AND codigo = ? LIMIT 1'
        );
        foreach ($productos as $codigoProducto) {
            $stmtProducto->bind_param('s', $codigoProducto);
            $stmtProducto->execute();
            $producto = $stmtProducto->get_result()->fetch_assoc();
            if (!$producto) {
                $stmtProducto->close();
                $conn->rollback();
                responder(400, ['success' => false, 'message' => 'Uno o más productos no existen o están inactivos.']);
            }
            $productosGuardados[] = $producto;
            $total += (float)$producto['precio'];
        }
        $stmtProducto->close();
    }

    $nombresProductos = array_map(static fn($p) => $p['nombre'], $productosGuardados);
    $productosTexto = implode(', ', $nombresProductos);
    $servicioDb = $servicio !== '' ? $servicio : null;

    $stmtVenta = $conn->prepare(
        'INSERT INTO VENTA (usuario_id, servicio, productos, total, fecha) VALUES (?, ?, ?, ?, NOW())'
    );
    $stmtVenta->bind_param('issd', $usuarioId, $servicioDb, $productosTexto, $total);
    $stmtVenta->execute();
    $ventaId = (int)$conn->insert_id;
    $stmtVenta->close();

    if (count($productosGuardados) > 0) {
        $stmtRelacion = $conn->prepare(
            'INSERT INTO VENTA_PRODUCTO (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, 1, ?)'
        );
        foreach ($productosGuardados as $producto) {
            $productoId = (int)$producto['id'];
            $precio = (float)$producto['precio'];
            $stmtRelacion->bind_param('iid', $ventaId, $productoId, $precio);
            $stmtRelacion->execute();
        }
        $stmtRelacion->close();
    }

    $conn->commit();
    responder(201, [
        'success' => true,
        'message' => 'Venta registrada correctamente.',
        'venta_id' => $ventaId,
        'total' => $total
    ]);
} catch (Throwable $e) {
    if ($conn->errno === 0 || $conn->ping()) {
        try { $conn->rollback(); } catch (Throwable $ignored) {}
    }
    error_log('Error al registrar venta: ' . $e->getMessage());
    responder(500, ['success' => false, 'message' => 'No fue posible registrar la venta.']);
}