<?php
// DATOS DE CONEXIÓN (MySQLi)

include_once "env.php";

// $host = "localhost"; 
// $dbname = "u225896339_driverhub"; // <--- PON TU NOMBRE DE BASE DE DATOS REAL AQUÍ
// $username = "u225896339_driverhub"; // <--- PON TU USUARIO REAL AQUÍ
// $password = "Vintara.xyz_2009";     // <--- PON TU CONTRASEÑA AQUÍ

// $host = $_ENV['DB_HOST'];
// $usuario = $_ENV['DB_USER'];
// $password = $_ENV['DB_PASSWORD'];
// $base_datos = $_ENV['DB_NAME'];

$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');


// Crear conexión estilo MySQLi (Compatible con tu auth_logic.php)
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Fallo fatal de conexión: " . $conn->connect_error);
}

// Configurar caracteres a UTF8 (para que las ñ y tildes se vean bien)
$conn->set_charset("utf8");

// Configurar zona horaria (Opcional, pero recomendado)
date_default_timezone_set('America/Mexico_City'); 
?>