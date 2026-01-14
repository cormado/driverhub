<?php
session_start();
// Ocultar errores visuales en producción
error_reporting(0);

// --- 1. LÓGICA DE IDIOMA (DICCIONARIO) ---
// Por defecto inglés, si no se especifica otra cosa
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; 

// Diccionario de textos
$trans = [
    'es' => [
        'page_title' => 'Iniciar Sesión - Vintara Logistics',
        'back_btn' => 'Volver',
        'secure_tag' => '/// TERMINAL_ACCESO_SEGURO ///',
        'login_title_1' => 'Acceso',
        'login_title_2' => 'Seguro',
        'label_user' => 'Usuario',
        'ph_user' => 'Introduce tu ID',
        'label_pass' => 'Contraseña',
        'ph_pass' => 'Introduce tu clave',
        'btn_connect' => 'CONECTAR',
        'footer_rights' => '© 2026 Vintara Logistics. Design by Vintara Studio.',
        'link_terms' => 'Términos de servicio',
        'link_privacy' => 'Política de privacidad',
        'url_terms' => '/es/terminos.html',
        'url_privacy' => '/es/privacidad.html',
        'url_downloads' => '/es/descargables.html',
        'nav_home' => 'Inicio',
        'nav_down' => 'Descargables',
        'nav_events' => 'Eventos',
        'error_pass' => 'Contraseña incorrecta',
        'error_user' => 'Usuario no encontrado',
        // Ban Screen
        'ban_title' => 'ESTÁS BANEADO',
        'ban_reason_label' => 'Razón de la sanción:',
        'ban_expire_label' => 'Tu acceso se restaurará el:',
        'ban_back_btn' => 'VOLVER AL INICIO',
    ],
    'en' => [
        'page_title' => 'Login - Vintara Logistics',
        'back_btn' => 'Back',
        'secure_tag' => '/// SECURE_ACCESS_TERMINAL ///',
        'login_title_1' => 'Secure',
        'login_title_2' => 'Access',
        'label_user' => 'Username',
        'ph_user' => 'Enter your ID',
        'label_pass' => 'Password',
        'ph_pass' => 'Enter your password',
        'btn_connect' => 'CONNECT',
        'footer_rights' => '© 2026 Vintara Logistics. Design by Vintara Studio.',
        'link_terms' => 'Terms of Service',
        'link_privacy' => 'Privacy Policy',
        'url_terms' => '/terms.html',
        'url_privacy' => '/privacy.html',
        'url_downloads' => '/downloads.html',
        'nav_home' => 'Home',
        'nav_down' => 'Downloads',
        'nav_events' => 'Events',
        'error_pass' => 'Incorrect password',
        'error_user' => 'User not found',
        // Ban Screen
        'ban_title' => 'YOU ARE BANNED',
        'ban_reason_label' => 'Ban Reason:',
        'ban_expire_label' => 'Access restored on:',
        'ban_back_btn' => 'BACK TO HOME',
    ]
];

// Si el idioma no es válido, forzar inglés
if (!array_key_exists($lang, $trans)) {
    $lang = 'en';
}
$t = $trans[$lang]; // Variable maestra de textos


// 2. INCLUDES NECESARIOS
require 'includes/db.php';
require 'includes/api.php'; 

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
                header("Location: index.php?msg=expulsado&lang=".$lang);
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

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['page_title']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    <script>
        // Verificamos si la URL ya tiene ?lang=
        const params = new URLSearchParams(window.location.search);
        
        // Si NO tiene lang, ejecutamos detección
        if (!params.has('lang')) {
            // 1. Verificar si hay preferencia guardada
            const pref = localStorage.getItem('userLang');
            if (pref) {
                 window.location.search = `?lang=${pref}`;
            } else {
                // 2. Si no, checar IP
                const paisesHispanos = ['AR', 'BO', 'CL', 'CO', 'CR', 'CU', 'DO', 'EC', 'SV', 'GT', 'HN', 'MX', 'NI', 'PA', 'PY', 'PE', 'PR', 'UY', 'VE'];
                
                fetch('https://ipapi.co/json/')
                .then(res => res.json())
                .then(data => {
                    if (paisesHispanos.includes(data.country_code)) {
                        window.location.search = '?lang=es';
                    } else {
                        window.location.search = '?lang=en';
                    }
                })
                .catch(err => {
                    // Fallback navegador
                    const nav = navigator.language || navigator.userLanguage;
                    if (nav.startsWith('es')) {
                        window.location.search = '?lang=es';
                    } else {
                        window.location.search = '?lang=en';
                    }
                });
            }
        } else {
            // Si YA tiene lang, guardamos la preferencia para el futuro
            localStorage.setItem('userLang', params.get('lang'));
        }

        // Función para cambiar idioma manualmente
        function cambiarIdioma(nuevoLang) {
            localStorage.setItem('userLang', nuevoLang);
            window.location.search = `?lang=${nuevoLang}`;
        }
    </script>
    
    <style>
        :root {
            --bg-primary: #0a0a0a;
            --text-main: #ffffff;
            --text-muted: #a0a0a0;
            --accent-purple: #9d4edd;
            --accent-red: #ff0055;
            --border-light: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-main);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative; 
        }

        /* --- PRELOADER --- */
        #preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--bg-primary); z-index: 9999;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            transition: opacity 0.8s ease, visibility 0.8s ease;
        }

        .loader-circle {
            width: 60px; height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1); border-top: 4px solid var(--accent-purple);
            border-radius: 50%; animation: spin 1s linear infinite;
            margin-bottom: 20px; box-shadow: 0 0 20px rgba(157, 78, 221, 0.3);
        }

        .loader-text {
            font-family: 'Kanit', sans-serif; font-size: 1.2rem; letter-spacing: 5px;
            text-transform: uppercase; animation: pulse 1.5s ease-in-out infinite;
        }
        
        .hide-loader { opacity: 0; visibility: hidden; pointer-events: none; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

        /* --- HEADER --- */
        header {
            position: fixed; top: 0; width: 100%; padding: 1.5rem 5%;
            display: flex; justify-content: space-between; align-items: center; z-index: 100;
            backdrop-filter: blur(5px);
        }

        .logo-text {
            font-family: 'Kanit', sans-serif; font-weight: 800; font-style: italic;
            font-size: 2rem; color: #fff; text-transform: uppercase; letter-spacing: -1px;
        }
        .logo-text span { color: var(--accent-purple); }

        .header-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .back-btn {
            padding: 0.5rem 1.5rem; border: 1px solid var(--accent-purple); color: #fff;
            text-decoration: none; font-family: 'Kanit', sans-serif; text-transform: uppercase;
            font-weight: 600; background: rgba(0,0,0,0.5); transition: 0.3s;
            clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px);
        }
        .back-btn:hover { background: var(--accent-purple); box-shadow: 0 0 15px var(--accent-purple); }

        .lang-btn {
            background: transparent; border: 1px solid #555; color: #aaa;
            padding: 0.5rem 1rem; cursor: pointer; font-family: 'Kanit', sans-serif;
            transition: 0.3s;
        }
        .lang-btn:hover { color: #fff; border-color: var(--accent-purple); }

        /* --- LOGIN AREA --- */
        .login-wrapper {
            flex: 1; display: flex; justify-content: center; align-items: center; position: relative;
            padding-top: 140px; padding-bottom: 50px;
            background: radial-gradient(circle at center, rgba(138,43,226,0.15) 0%, #0a0a0a 80%), 
                        url('assets/img/header.png') center/cover no-repeat;
        }

        .login-wrapper::before {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background: rgba(10, 10, 10, 0.6); z-index: 0;
        }

        .glass-login {
            background: rgba(10, 10, 10, 0.85); border: 1px solid rgba(255,255,255,0.1);
            padding: 4rem 3rem; width: 100%; max-width: 480px;
            backdrop-filter: blur(20px); position: relative; z-index: 10;
            box-shadow: 0 0 40px rgba(0,0,0,0.8);
            border-bottom: 3px solid var(--accent-purple);
            opacity: 0; transform: translateY(50px);
            animation: hudEntry 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards 0.5s;
        }

        @keyframes hudEntry { to { opacity: 1; transform: translateY(0); } }

        .login-title {
            font-family: 'Kanit', sans-serif; font-size: 3rem; text-align: center;
            text-transform: uppercase; margin-bottom: 2rem; color: #fff; font-style: italic;
        }
        
        .login-title span { 
            color: var(--accent-purple); position: relative; display: inline-block;
            animation: chromaticMove 3s infinite;
            text-shadow: 2px 0 0 rgba(255,0,0,0.7), -2px 0 0 rgba(0,0,255,0.7);
        }

        @keyframes chromaticMove {
            0% { text-shadow: 2px 0 0 rgba(255,0,0,0.7), -2px 0 0 rgba(0,0,255,0.7); transform: translate(0); }
            2% { text-shadow: 2px 0 0 rgba(255,0,0,0.7), -2px 0 0 rgba(0,0,255,0.7); transform: translate(-2px, 1px); }
            4% { text-shadow: 2px 0 0 rgba(255,0,0,0.7), -2px 0 0 rgba(0,0,255,0.7); transform: translate(0); }
            100% { text-shadow: 2px 0 0 rgba(255,0,0,0.7), -2px 0 0 rgba(0,0,255,0.7); transform: translate(0); }
        }

        .error-box {
            background: rgba(255, 0, 0, 0.1); border: 1px solid #ff4d4d; color: #ff4d4d;
            padding: 10px; margin-bottom: 20px; text-align: center;
            font-family: 'Kanit', sans-serif; font-size: 0.9rem; text-transform: uppercase;
            box-shadow: 0 0 10px rgba(255,0,0,0.2); animation: pulseRed 2s infinite;
        }
        @keyframes pulseRed { 0%,100% {opacity:1;} 50% {opacity:0.7;} }

        .form-group { margin-bottom: 2rem; }
        .form-label {
            display: block; margin-bottom: 0.8rem; font-family: 'Kanit', sans-serif;
            font-size: 1rem; color: var(--accent-purple); text-transform: uppercase;
            letter-spacing: 2px; font-weight: 600;
        }

        .input-group-inner { position: relative; width: 100%; }
        .form-input {
            width: 100%; padding: 1.2rem 1.2rem; padding-right: 3rem;
            background: rgba(0, 0, 0, 0.4); border: 2px solid #333; color: #fff;
            font-family: 'Montserrat', sans-serif; font-size: 1.1rem;
            transition: all 0.1s ease;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 15px 100%, 0 calc(100% - 15px));
        }
        .form-input:focus {
            outline: none; border-color: var(--accent-purple);
            background: rgba(20, 0, 20, 0.6); box-shadow: 0 0 20px rgba(157, 78, 221, 0.3);
        }
        .form-input.vibrating { animation: inputShake 0.2s linear infinite; border-color: #fff !important; }
        @keyframes inputShake { 0% { transform: translate(0px, 0px); } 25% { transform: translate(2px, -2px); } 50% { transform: translate(-2px, 2px); } 75% { transform: translate(2px, 2px); } 100% { transform: translate(-2px, -2px); } }

        .input-icon {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            color: #555; font-size: 1.2rem; pointer-events: none; transition: 0.3s;
        }
        .form-input:focus + .input-icon { color: var(--accent-purple); text-shadow: 0 0 10px var(--accent-purple); }

        .submit-btn {
            width: 100%; padding: 1.5rem; margin-top: 1rem;
            background: var(--text-main); color: #000; border: none;
            font-family: 'Kanit', sans-serif; text-transform: uppercase;
            font-size: 1.5rem; font-weight: 900; font-style: italic; cursor: pointer;
            position: relative; overflow: hidden;
            clip-path: polygon(20px 0, 100% 0, 100% calc(100% - 20px), calc(100% - 20px) 100%, 0 100%, 0 20px);
            transition: 0.3s;
        }
        .submit-btn:hover {
            background: var(--accent-purple); color: #fff;
            box-shadow: 0 0 30px var(--accent-purple); transform: scale(1.02);
        }

        /* --- PARTÍCULAS --- */
        .particle {
            position: absolute; pointer-events: none; width: 6px; height: 6px;
            border-radius: 50%; animation: particleExplosion 0.8s ease-out forwards; z-index: 1000;
        }
        @keyframes particleExplosion { 0% { opacity: 1; transform: translate(0, 0) scale(1); box-shadow: 0 0 10px currentColor; } 100% { opacity: 0; transform: translate(var(--tx), var(--ty)) scale(0); } }

        /* --- FOOTER --- */
        footer { background: #050505; padding: 3rem; text-align: center; border-top: 1px solid #222; z-index: 10; }
        .social-links { margin: 2rem 0; }
        .social-links a { color: var(--text-muted); font-size: 1.8rem; margin: 0 1rem; transition: all 0.3s; }
        .social-links a:hover { color: var(--accent-purple); transform: translateY(-3px); }
        .footer-links { margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); }
        .footer-links a { color: var(--text-muted); text-decoration: none; margin: 0 1rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; transition: color 0.3s; }
        .footer-links a:hover { color: #fff; text-decoration: underline; }

        @media (max-width: 768px) { .glass-login { padding: 2rem; max-width: 90%; } .login-title { font-size: 2.2rem; } }
    </style>
</head>
<body>

    <div id="preloader">
        <div class="loader-circle"></div>
        <div class="loader-text">Loading...</div>
    </div>

    <header id="header">
        <div class="logo-text">VINTARA<span>.</span></div>
        <div class="header-controls">
            <?php if($lang == 'es'): ?>
                <button onclick="cambiarIdioma('en')" class="lang-btn"><i class="fas fa-globe"></i> EN</button>
            <?php else: ?>
                <button onclick="cambiarIdioma('es')" class="lang-btn"><i class="fas fa-globe"></i> ES</button>
            <?php endif; ?>

            <a href="/" class="back-btn"><i class="fas fa-arrow-left"></i> <?php echo $t['back_btn']; ?></a>
        </div>
    </header>

    <div class="login-wrapper">
        <div class="glass-login">
            <div style="position: absolute; top: 15px; left: 20px; color: #555; font-family: 'Kanit'; font-size: 0.7rem; letter-spacing: 2px;">
                <?php echo $t['secure_tag']; ?>
            </div>

            <h2 class="login-title"><?php echo $t['login_title_1']; ?> <span><?php echo $t['login_title_2']; ?></span></h2>
            
            <?php if($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label class="form-label" for="username"><?php echo $t['label_user']; ?></label>
                    <div class="input-group-inner">
                        <input type="text" id="username" name="username" class="form-input trigger-explosion" placeholder="<?php echo $t['ph_user']; ?>" required autocomplete="off">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password"><?php echo $t['label_pass']; ?></label>
                    <div class="input-group-inner">
                         <input type="password" id="password" name="password" class="form-input trigger-explosion" placeholder="<?php echo $t['ph_pass']; ?>" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" class="submit-btn"><?php echo $t['btn_connect']; ?></button>
            </form>
        </div>
    </div>

    <footer>
        <p style="opacity: 0.5;"><?php echo $t['footer_rights']; ?></p>
        <div class="social-links">
            <a href="https://www.tiktok.com/@chapulinesvtc" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://discord.gg/UY42pmqvnw" target="_blank"><i class="fab fa-discord"></i></a>
            <a href="https://www.youtube.com/@chapulinesvtc" target="_blank"><i class="fab fa-youtube"></i></a>
        </div>
        <div class="footer-links">
            <a href="<?php echo $t['url_terms']; ?>"><?php echo $t['link_terms']; ?></a>
            <a href="<?php echo $t['url_privacy']; ?>"><?php echo $t['link_privacy']; ?></a>
        </div>
    </footer>

    <script>
        // --- 1. PRELOADER ---
        window.addEventListener('load', () => { setTimeout(() => { document.getElementById('preloader').classList.add('hide-loader'); }, 800); });
        // --- 2. SISTEMA DE EXPLOSIÓN ---
        const explosionInputs = document.querySelectorAll('.trigger-explosion');
        const colors = ['#9d4edd', '#ff0055', '#ffffff'];
        explosionInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                input.classList.add('vibrating'); setTimeout(() => input.classList.remove('vibrating'), 200); spawnParticles(input);
            });
        });
        function spawnParticles(targetElement) {
            const rect = targetElement.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2 + window.scrollY;
            for (let i = 0; i < 12; i++) { createParticle(centerX, centerY); }
        }
        function createParticle(x, y) {
            const particle = document.createElement('div');
            particle.classList.add('particle'); document.body.appendChild(particle);
            const color = colors[Math.floor(Math.random() * colors.length)];
            particle.style.backgroundColor = color; particle.style.color = color; 
            particle.style.left = `${x}px`; particle.style.top = `${y}px`;
            const angle = Math.random() * Math.PI * 2; const velocity = 60 + Math.random() * 90; 
            const tx = Math.cos(angle) * velocity; const ty = Math.sin(angle) * velocity;
            particle.style.setProperty('--tx', `${tx}px`); particle.style.setProperty('--ty', `${ty}px`);
            particle.addEventListener('animationend', () => { particle.remove(); });
        }
    </script>
</body>
</html>