<?php
// DATOS DE CONEXIÓN (Configuración para XAMPP)
$host = 'localhost';       // El servidor es tu propia PC
$usuario = 'root';         // Usuario por defecto de XAMPP
$password = '';            // XAMPP no trae contraseña por defecto
$base_datos = 'vintara_db'; // El nombre exacto que pusimos en phpMyAdmin

// CREAR LA CONEXIÓN
$conn = new mysqli($host, $usuario, $password, $base_datos);

// VERIFICAR SI HUBO ERROR
if ($conn->connect_error) {
    // Si falla, mata la página y muestra el error
    die("❌ Error fatal de conexión: " . $conn->connect_error);
}

// Opcional: Si quieres probar que funciona, descomenta la línea de abajo quitando las //
// echo "✅ Conexión exitosa a la base de datos";
?>