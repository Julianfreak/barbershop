<?php
header('Content-Type: application/json');

// Conexión a la base de datos
require_once 'conexion.php';

// Verificar si se envió el ID
if (isset($_POST['id'])) {
    $id_usuario = intval($_POST['id']);

    // Preparar y ejecutar la eliminación
    $sql = "DELETE FROM USUARIO WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        if ($stmt->execute()) {
            echo json_encode([
                "success" => true,
                "message" => "Usuario eliminado correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Error al eliminar el usuario"
            ]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Error en la consulta SQL"
        ]);
    }

    $conn->close();
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se proporcionó un ID de usuario"
    ]);
}
?>
