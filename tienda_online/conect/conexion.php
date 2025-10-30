<?php
$servidor = "localhost";
$usuario = "root";
$password = ""; // La contraseña puede estar vacía si no configuraste una
$base_datos = "nombre_de_tu_base_de_datos"; // Reemplaza con el nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("La conexión falló: " . $conn->connect_error);
}
echo "Conexión exitosa";
?>
