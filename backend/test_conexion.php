<?php
include('conexion.php');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    echo "Conexión exitosa a la base de datos MySQL desde PHP.";
}
?>
