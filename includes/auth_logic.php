<?php
session_start();
// Ocultar errores visuales en producción
error_reporting(0);

// 2. INCLUDES NECESARIOS
require_once 'includes/db.php'; // Use require_once
require_once 'includes/api.php'; 

// 3. FUNCIÓN DE BANEO (Mofificada para aceptar idioma)
function mostrarPantallaBaneo($razon, $fecha_fin, $txt) {
    echo "
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&display=swap');
        body { margin:0; background:#0a0a0a; color:white; height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; font-family:'Kanit', sans-serif; text-align:center; }
    </style>
    <div style='padding:20px;'>
        <img src='assets/img/logo.png' style='width:100px; margin-bottom:20px; opacity:0.5;'>
        <h1 style='color:#ff0055; font-size:3rem; font-style:italic; margin:0;'>{$txt['ban_title']}</h1>
        <div style='background:#1a1a1a; padding:30px; border:1px solid #333; border-radius:10px; max-width:500px; margin-top:20px;'>
            <p style='color:#aaa; text-transform:uppercase; font-size:0.8rem; margin-bottom:5px;'>{$txt['ban_reason_label']}</p>
            <p style='font-size:1.2rem; margin-bottom:20px;'>{$razon}</p>
            <p style='color:#aaa; text-transform:uppercase; font-size:0.8rem; margin-bottom:5px;'>{$txt['ban_expire_label']}</p>
            <p style='font-size:1.1rem; color:#9d4edd;'>{$fecha_fin->format('d/m/Y H:i')}</p>
        </div>
        <a href='index.php?lang={$_GET['lang']}' style='display:inline-block; margin-top:30px; color:#666; text-decoration:none; border:1px solid #333; padding:10px 20px; transition:0.3s;'>{$txt['ban_back_btn']}</a>
    </div>
    ";
}

// 4. LÓGICA DE SANCIONES
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT banned_until, ban_reason, tmp_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $check = $stmt->get_result()->fetch_assoc();

    if ($check && $check['banned_until'] != NULL) {
        $now = new DateTime();
        $ban_end = new DateTime($check['banned_until']);

        if ($now < $ban_end) {
            mostrarPantallaBaneo($check['ban_reason'], $ban_end, $t);
            exit();
        } else {
            $sigue_en_vtc = verificar_usuario_en_vtc($check['tmp_id']); 
            if ($sigue_en_vtc) {
                $conn->query("UPDATE users SET banned_until = NULL, ban_reason = NULL WHERE id = $uid");
            } else {
                $conn->query("DELETE FROM users WHERE id = $uid");
                session_destroy();
                // Assumes $lang is available in global scope (from i18n.php)
                header("Location: index.php?msg=expulsado&lang=".$GLOBALS['lang']);
                exit();
            }
        }
    }
    header("Location: dashboard.php");
    exit();
}

$error = '';

// 5. PROCESO DE LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role, banned_until, ban_reason FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            
            $esta_baneado = false;
            if ($row['banned_until'] != NULL) {
                $now = new DateTime();
                $ban_end = new DateTime($row['banned_until']);
                if ($now < $ban_end) {
                    $esta_baneado = true;
                    mostrarPantallaBaneo($row['ban_reason'], $ban_end, $t);
                    exit();
                } else {
                    $uid_temp = $row['id'];
                    $conn->query("UPDATE users SET banned_until = NULL, ban_reason = NULL WHERE id = $uid_temp");
                }
            }

            if (!$esta_baneado) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                header("Location: dashboard.php");
                exit();
            }

        } else {
            $error = $t['error_pass'];
        }
    } else {
        $error = $t['error_user'];
    }
}
?>
