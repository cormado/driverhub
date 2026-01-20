<?php
session_start();

// 1. CONEXIÓN A LA BASE DE DATOS
require_once 'includes/db.php'; 

// 2. LÓGICA DE LOGIN
$error = "";

// --- CORRECCIÓN 1: SI YA ESTÁ LOGUEADO ---
if(isset($_SESSION['user_id'])) {
    // Ya no buscamos carpetas, vamos directo al archivo
    header("Location: dashboard.php");
    exit;
}

// Procesar Formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // MySQLi QUERY
    $sql = "SELECT id, username, password, role, avatar_url FROM users WHERE username = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if ($usuario && password_verify($pass, $usuario['password'])) {
            // DATOS DE SESIÓN
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['role'] = $usuario['role'];
            $_SESSION['avatar'] = $usuario['avatar_url'];

            // --- CORRECCIÓN 2: REDIRECCIÓN DE LOGIN EXITOSO ---
            // Sin importar el rol, todos van al dashboard principal.
            // El dashboard.php se encargará de mostrar cosas diferentes según el rol.
            header("Location: dashboard.php");
            exit;
            
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
        $stmt->close();
    } else {
        $error = "Error en el sistema: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriverHub Login - Vintara</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="../img/LOGO.png">

    <style>
        :root { --accent: #bd00ff; --bg: #030303; --glass: rgba(255, 255, 255, 0.05); }
        body { margin: 0; padding: 0; background: var(--bg); font-family: 'Montserrat', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; 
               background-image: radial-gradient(circle at 20% 80%, rgba(189, 0, 255, 0.1) 0%, transparent 30%); }
        
        .login-wrapper { width: 100%; max-width: 400px; padding: 20px; position: relative; z-index: 10; }
        .glass-login { background: var(--glass); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 40px; box-shadow: 0 0 50px rgba(0,0,0,0.5); text-align: center; position: relative; overflow: hidden; }
        .glass-login::before { content: ''; position: absolute; top:0; left:0; width: 100%; height: 3px; background: linear-gradient(90deg, transparent, var(--accent), transparent); }
        
        .logo-text { font-family: 'Kanit'; font-weight: 800; font-size: 2rem; margin-bottom: 10px; font-style: italic; }
        .logo-text span { color: var(--accent); }
        .login-title { font-size: 1.5rem; margin-bottom: 30px; font-weight: 600; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-label { font-size: 0.8rem; color: #aaa; margin-left: 10px; display: block; margin-bottom: 5px; }
        .input-group-inner { position: relative; }
        .form-input { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid #333; padding: 15px 15px 15px 45px; border-radius: 50px; color: #fff; box-sizing: border-box; font-family: 'Montserrat'; transition: 0.3s; }
        .form-input:focus { outline: none; border-color: var(--accent); background: rgba(0,0,0,0.5); box-shadow: 0 0 15px rgba(189, 0, 255, 0.2); }
        .input-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #666; }
        .submit-btn { width: 100%; background: #fff; color: #000; border: none; padding: 15px; border-radius: 50px; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: 0.3s; margin-top: 10px; font-family: 'Kanit'; font-style: italic; letter-spacing: 1px; }
        .submit-btn:hover { background: var(--accent); color: #fff; box-shadow: 0 0 25px var(--accent); transform: scale(1.02); }
        .error-box { background: rgba(255, 68, 68, 0.1); border: 1px solid #ff4444; color: #ff4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .back-btn { position: absolute; top: 20px; left: 20px; color: #aaa; text-decoration: none; font-size: 0.9rem; transition: 0.3s; }
        .back-btn:hover { color: #fff; }
    </style>
</head>
<body>

    <a href="/" class="back-btn"><i class="fas fa-arrow-left"></i> Volver</a>

    <div class="login-wrapper">
        <div class="glass-login">
            <div class="logo-text">VINTARA<span>.</span></div>
            <h2 class="login-title">DriverHub Access</h2>

            <?php if ($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">USUARIO</label>
                    <div class="input-group-inner">
                        <input type="text" id="username" name="username" class="form-input" placeholder="Ej: Owner" required autocomplete="off">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">CONTRASEÑA</label>
                    <div class="input-group-inner">
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn">CONECTAR</button>
            </form>
        </div>
        <p style="text-align: center; margin-top: 20px; font-size: 0.8rem; opacity: 0.5;">© Vintara VTC System</p>
    </div>

</body>
</html>