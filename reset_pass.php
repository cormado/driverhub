<?php
require 'includes/db.php';

// 1. Definimos la nueva contraseña que queremos usar
$password_real = "12345"; 

// 2. La encriptamos usando el algoritmo seguro de PHP
$password_encriptada = password_hash($password_real, PASSWORD_DEFAULT);

// 3. Actualizamos al usuario 'admin' en la base de datos
$sql = "UPDATE users SET password = '$password_encriptada' WHERE username = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "<h1>✅ Contraseña actualizada correctamente</h1>";
    echo "<p>Usuario: <b>admin</b></p>";
    echo "<p>Nueva Contraseña: <b>12345</b></p>";
    echo "<br><a href='index.php'>Ir al Login</a>";
} else {
    echo "Error: " . $conn->error;
}
?>