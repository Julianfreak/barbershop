<?php
header('Content-Type: application/json');
require 'conexion.php';

session_start();

try {
    // Leer JSON o POST normal
    $input = file_get_contents("php://input");
    $data = json_decode($input, true) ?? $_POST;

    // Obtener usuario_id desde sesión o desde el payload
    $usuario_id = $_SESSION['usuario_id'] ?? ($data['usuario_id'] ?? null);

    // Obtener productos y otros datos
    $productos = $data['productos'] ?? [];
    $servicio = $data['servicio'] ?? null;
    $total = $data['total'] ?? null;

    // Validar datos mínimos
    if (!$usuario_id || empty($productos) || !$total) {
        echo json_encode(["success" => false, "message" => "Datos incompletos"]);
        exit;
    }

    // Aquí tu lógica de inserción en base de datos
    $stmt = $conexion->prepare("
        INSERT INTO VENTA (usuario_id, servicio, total, fecha)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("isd", $usuario_id, $servicio, $total);
    $stmt->execute();

    $venta_id = $stmt->insert_id;

    // Insertar productos asociados
    $stmtProd = $conexion->prepare("
        INSERT INTO VENTA_PRODUCTO (venta_id, producto_id)
        VALUES (?, ?)
    ");
    foreach ($productos as $producto_id) {
        $stmtProd->bind_param("ii", $venta_id, $producto_id);
        $stmtProd->execute();
    }

    echo json_encode(["success" => true, "message" => "Venta registrada correctamente", "venta_id" => $venta_id]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
