<?php
// Activar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Probando conexión...</h1>";

// Intenta cargar db.php
if (file_exists('includes/db.php')) {
    require 'includes/db.php';
    echo "✅ Archivo db.php cargado.<br>";
} else {
    die("❌ NO encuentro includes/db.php");
}

// Probar si la variable $conn existe y conecta
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->connect_error) {
        die("❌ Error de Conexión MySQL: " . $conn->connect_error);
    }
    echo "✅ ¡CONEXIÓN EXITOSA A LA BASE DE DATOS! 🚀<br>";
    echo "Host info: " . $conn->host_info;
} else {
    die("❌ La variable \$conn no se creó correctamente.");
}
?>