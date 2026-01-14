<?php
session_start();
error_reporting(0); 
require 'includes/db.php';
require 'includes/api.php'; // Aquí se cargan $evento_destacado y $eventos_asistencia

// --- 1. IDIOMA ---
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$trans = [
    'es' => [
        'nav_admin' => 'Admin', 'nav_manage_events' => 'Gestionar Eventos', 'nav_users' => 'Usuarios',
        'nav_menu' => 'Menú', 'nav_ops' => 'Centro de Operaciones', 'nav_workshop' => 'Taller Vintara',
        'nav_tickets' => 'Soporte / Tickets', 'nav_store' => 'Tienda', 'nav_members' => 'Miembros', 'nav_recruiting' => 'Recluta',
        'logout' => 'Cerrar Sesión', 'title_ops' => 'CENTRO DE OPERACIONES', 'title_events' => 'GESTOR DE EVENTOS',
        'title_users' => 'GESTIÓN DE USUARIOS', 'title_workshop' => 'TALLER OFICIAL', 'title_tickets' => 'SOPORTE',
        'title_store' => 'TIENDA', 'stats_total' => 'MIEMBROS TOTALES', 'stats_verified' => 'VERIFICADA',
        'event_official' => '★ PRÓXIMO EVENTO OFICIAL', 'event_agenda' => 'Agenda de Asistencia',
        'event_invited' => 'Convoys Invitados', 'event_none' => 'No hay eventos en la agenda.',
        'workshop_paint' => 'Pintura Oficial', 'workshop_truck' => 'CAMIÓN', 'workshop_trailer' => 'REMOLQUE',
        'workshop_tag' => 'Tag Oficial', 'workshop_id' => 'Identificación', 'workshop_guide' => 'GUIA DEL CONDUCTOR',
        'workshop_soon' => 'GUIA: PROXIMAMENTE', 'store_construction' => 'En Construcción',
        'store_soon' => 'El módulo de Tienda Vintara estará disponible pronto.',
        'ban_denied' => 'ACCESO DENEGADO', 'ban_suspended' => 'CUENTA SUSPENDIDA', 'ban_reason' => 'Motivo:', 'ban_release' => 'Liberación:'
    ],
    'en' => [
        'nav_admin' => 'Admin', 'nav_manage_events' => 'Manage Events', 'nav_users' => 'Users',
        'nav_menu' => 'Menu', 'nav_ops' => 'Operations Center', 'nav_workshop' => 'Vintara Workshop',
        'nav_tickets' => 'Support / Tickets', 'nav_store' => 'Store', 'nav_members' => 'Members', 'nav_recruiting' => 'Recruiting',
        'logout' => 'Logout', 'title_ops' => 'OPERATIONS CENTER', 'title_events' => 'EVENT MANAGER',
        'title_users' => 'USER MANAGEMENT', 'title_workshop' => 'OFFICIAL WORKSHOP', 'title_tickets' => 'SUPPORT',
        'title_store' => 'STORE', 'stats_total' => 'TOTAL MEMBERS', 'stats_verified' => 'VERIFIED',
        'event_official' => '★ NEXT OFFICIAL EVENT', 'event_agenda' => 'Attendance Agenda',
        'event_invited' => 'Guest Convoys', 'event_none' => 'No events in the agenda.',
        'workshop_paint' => 'Official Paintjob', 'workshop_truck' => 'TRUCK', 'workshop_trailer' => 'TRAILER',
        'workshop_tag' => 'Official Tag', 'workshop_id' => 'Identification', 'workshop_guide' => 'DRIVER GUIDE',
        'workshop_soon' => 'GUIDE: COMING SOON', 'store_construction' => 'Under Construction',
        'store_soon' => 'Vintara Store module will be available soon.',
        'ban_denied' => 'ACCESS DENIED', 'ban_suspended' => 'ACCOUNT SUSPENDID', 'ban_reason' => 'Reason:', 'ban_release' => 'Release:'
    ]
];
if (!array_key_exists($lang, $trans)) { $lang = 'en'; }
$t = $trans[$lang];

// 2. SEGURIDAD
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// 3. BAN CHECK
$uid_check = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT banned_until, ban_reason, tmp_id FROM users WHERE id = ?");
$stmt->bind_param("i", $uid_check);
$stmt->execute();
$check_ban = $stmt->get_result()->fetch_assoc();

if ($check_ban && $check_ban['banned_until'] != NULL) {
    $now = new DateTime();
    $ban_end = new DateTime($check_ban['banned_until']);
    if ($now < $ban_end) {
        die("<style>@import url('https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&display=swap'); body{margin:0;background:#0a0a0a;color:white;font-family:'Kanit',sans-serif;}</style><div style='height:100vh; display:flex; align-items:center; justify-content:center; flex-direction:column; text-align:center; padding:20px;'><img src='assets/img/logo.png' style='width:100px; margin-bottom:20px; opacity:0.5;'><h1 style='color:#ff0055; font-size:3rem; font-style:italic; margin:0;'>{$t['ban_denied']}</h1><h2 style='color:white; font-size:1.5rem; margin-top:0;'>{$t['ban_suspended']}</h2><div style='background:#1a1a1a; padding:30px; border:1px solid #333; border-radius:10px; max-width:500px; margin-top:20px;'><p style='color:#aaa; text-transform:uppercase; font-size:0.8rem; margin-bottom:5px;'>{$t['ban_reason']}</p><p style='font-size:1.2rem; margin-bottom:20px;'>{$check_ban['ban_reason']}</p><p style='color:#aaa; text-transform:uppercase; font-size:0.8rem; margin-bottom:5px;'>{$t['ban_release']}</p><p style='font-size:1.1rem; color:#9d4edd;'>{$ban_end->format('d/m/Y H:i')}</p></div><a href='logout.php' style='margin-top:30px; color:#666; text-decoration:none; border:1px solid #333; padding:10px 20px; text-transform:uppercase;'>{$t['logout']}</a></div>");
    } else {
        $sigue_en_vtc = verificar_usuario_en_vtc($check_ban['tmp_id']); 
        if ($sigue_en_vtc) { $conn->query("UPDATE users SET banned_until = NULL, ban_reason = NULL WHERE id = $uid_check"); } 
        else { $conn->query("DELETE FROM users WHERE id = $uid_check"); session_destroy(); header("Location: index.php?msg=expulsado&lang=$lang"); exit(); }
    }
}

// 4. LOGICA DASHBOARD
$rol = $_SESSION['role'];
$es_owner = ($rol === 'owner');
$es_admin = ($rol === 'admin' || $es_owner);
$vista = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// LOGICA BORRAR USUARIO
if ($es_admin && isset($_GET['del_user'])) {
    $id_a_borrar = intval($_GET['del_user']);
    if ($id_a_borrar != $_SESSION['user_id']) {
        if (!$es_owner) {
            $check = $conn->query("SELECT role FROM users WHERE id = $id_a_borrar")->fetch_assoc();
            if ($check['role'] === 'owner') { header("Location: dashboard.php?view=database&lang=$lang"); exit(); }
        }
        $conn->query("DELETE FROM users WHERE id = $id_a_borrar");
    }
    header("Location: dashboard.php?view=database&lang=$lang"); exit();
}

// LOGICA AGREGAR EVENTO
$discord_template = "";
$mensaje_accion = "";
if ($es_admin && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event_id'])) {
    $nuevo_id = intval($_POST['add_event_id']);
    $datos_evento = conectarTruckersMP("/events/$nuevo_id");
    if (isset($datos_evento['response'])) {
        $evt = $datos_evento['response'];
        $salida = is_array($evt['departure']) ? $evt['departure']['city'] : $evt['departure'];
        $destino = is_array($evt['arrive']) ? $evt['arrive']['city'] : $evt['arrive'];
        $mapa = $evt['map'] ?? 'No disponible';
        $dlc = 'Base (Ninguno)';
        $check = $conn->query("SELECT id FROM event_ids WHERE event_id = $nuevo_id");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO event_ids (event_id) VALUES ($nuevo_id)");
            $mensaje_accion = "✅ Evento agregado.";
            $discord_template = "**Tenemos un nuevo convoy en nuestro calendario!**\n\nRecuerda entrar minimo 10 minutos antes...\nInformacion:\n\n`🟢` | **Camion a usar:** Libre con pintura oficial\n`🟢` | **Remolque a usar:** Aislado con pintura oficial\n`🟡` | **DLC a usar:** " . $dlc . "\n`🟡` | **Ubicacion de salida:** " . $salida . "\n`🟡` | **Ubicacion de destino:** " . $destino . "\n`🔵` | **Mapa de la ruta:** " . $mapa . "\n`🔵` | **Organizador:** " . $evt['vtc']['name'] . "\n`🔵` | **Servidor:** " . $evt['server']['name'];
        } else { $mensaje_accion = "⚠️ Ya registrado."; }
    } else { $mensaje_accion = "❌ Error ID."; }
}
if ($es_admin && isset($_GET['del_event'])) {
    $conn->query("DELETE FROM event_ids WHERE event_id = ".intval($_GET['del_event']));
    header("Location: dashboard.php?view=events&lang=$lang"); exit();
}
$lista_eventos_db = [];
if ($es_admin) {
    $res = $conn->query("SELECT * FROM event_ids ORDER BY id DESC");
    while($row = $res->fetch_assoc()) { $lista_eventos_db[] = $row; }
}

// DATOS USUARIO
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = $user_id";
$res_user = $conn->query($sql_user);
$datos_usuario = $res_user->fetch_assoc();
$mi_avatar = $datos_usuario['avatar_url'] ? $datos_usuario['avatar_url'] : 'assets/img/logo.png';
$mi_rango_vtc = $datos_usuario['vtc_rank'] ? $datos_usuario['vtc_rank'] : 'Driver';
$mi_tmp_id = $datos_usuario['tmp_id'] ? $datos_usuario['tmp_id'] : '---';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title>Vintara Terminal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,400;0,600;0,800;1,800&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script>
        function cambiarIdioma(nuevoLang) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', nuevoLang);
            window.location.href = url.toString();
        }
    </script>
    <style>
        :root { --bg-color: #050505; --accent-purple: #9d4edd; --accent-green: #00ff9d; --accent-gold: #ffd700; --accent-red: #ff0055; --text-grey: #888; --sidebar-width: 280px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: var(--bg-color); color: white; font-family: 'Montserrat', sans-serif; min-height: 100vh; background-image: linear-gradient(rgba(0,0,0,0.92), rgba(0,0,0,0.97)), url('assets/img/header.png'); background-size: cover; background-position: center; background-attachment: fixed; display: flex; }
        .sidebar { width: var(--sidebar-width); border-right: 1px solid rgba(255,255,255,0.05); padding: 30px; display: flex; flex-direction: column; background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); position: fixed; height: 100vh; z-index: 100; overflow-y: auto; }
        .user-credential { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 50px; height: 50px; border-radius: 8px; border: 2px solid var(--accent-purple); object-fit: cover; }
        .user-info h4 { font-family: 'Kanit'; font-size: 0.9rem; margin-bottom: 2px; color: white; line-height: 1; }
        .user-rank { font-size: 0.7rem; color: var(--accent-green); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 2px;}
        .user-id { font-size: 0.65rem; color: #666; font-family: monospace; display: block; }
        .brand { font-family: 'Kanit', sans-serif; font-weight: 800; font-style: italic; font-size: 1.8rem; letter-spacing: -1px; margin-bottom: 30px; }
        .brand span { color: var(--accent-purple); font-size: 1rem; margin-left: 5px; font-weight: 400; }
        .vtc-mini-widget { background: rgba(255,255,255,0.03); border: 1px solid var(--accent-purple); padding: 15px; margin-bottom: 30px; text-align: center; }
        .vtc-logo-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid var(--accent-purple); }
        .nav-category { font-family: 'Kanit'; font-size: 0.7rem; color: var(--accent-purple); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; margin-top: 10px; }
        .nav-link { display: flex; align-items: center; color: var(--text-grey); text-decoration: none; padding: 12px 15px; margin-bottom: 5px; font-size: 0.9rem; transition: 0.3s; border-left: 2px solid transparent; }
        .nav-link:hover, .nav-link.active { color: white; border-left-color: var(--accent-green); background: linear-gradient(90deg, rgba(0,255,157,0.05), transparent); padding-left: 20px; }
        .nav-link i { width: 25px; margin-right: 10px; }
        .lang-switcher-sidebar { display: flex; gap: 10px; margin-bottom: 20px; }
        .lang-mini-btn { background: rgba(255,255,255,0.05); border: 1px solid #333; color: #888; padding: 5px 10px; font-size: 0.7rem; cursor: pointer; font-family: 'Kanit'; }
        .lang-mini-btn.active { border-color: var(--accent-purple); color: white; background: rgba(157, 78, 221, 0.1); }
        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 40px 60px; }
        .top-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .page-title { font-family: 'Kanit', sans-serif; font-weight: 800; font-style: italic; font-size: 2.5rem; line-height: 1; text-transform: uppercase; }
        .logout-btn { border: 1px solid var(--text-grey); color: var(--text-grey); padding: 8px 20px; text-decoration: none; font-size: 0.75rem; text-transform: uppercase; transition: 0.3s; }
        .logout-btn:hover { border-color: #ff0055; color: #ff0055; }
        .hero-section { display: grid; grid-template-columns: 250px 1fr; gap: 30px; margin-bottom: 50px; }
        .vtc-stat-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); padding: 30px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-top: 3px solid var(--accent-green); height: 100%; }
        .vtc-big-number { font-family: 'Kanit'; font-size: 4rem; line-height: 1; color: white; text-shadow: 0 0 20px rgba(0,255,157,0.3); }
        .vtc-logo-big { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; border: 2px solid var(--accent-green); }
        .featured-event-card { background: rgba(157, 78, 221, 0.05); border: 1px solid var(--accent-purple); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; padding: 40px; min-height: 320px; }
        .event-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, #050505 10%, rgba(5,5,5,0.3) 100%); z-index: 1; }
        .event-content { position: relative; z-index: 2; }
        .tag-hosted { background: var(--accent-purple); color: white; padding: 5px 15px; font-family: 'Kanit'; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; display: inline-block; margin-bottom: 15px; box-shadow: 0 0 15px var(--accent-purple); }
        .event-title { font-family: 'Kanit'; font-size: 3rem; font-style: italic; line-height: 1; margin-bottom: 15px; text-transform: uppercase; text-shadow: 0 5px 10px rgba(0,0,0,0.8); }
        .event-details { display: flex; gap: 30px; font-size: 0.9rem; color: #fff; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .event-detail-item i { color: var(--accent-purple); margin-right: 8px; }
        .btn-event { position: absolute; top: 30px; right: 30px; z-index: 2; border: 1px solid white; color: white; padding: 10px 20px; text-decoration: none; text-transform: uppercase; font-family: 'Kanit'; font-size: 0.8rem; transition: 0.3s; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
        .attending-section-title { font-family: 'Kanit'; font-size: 1.2rem; text-transform: uppercase; color: var(--text-grey); margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .attending-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .attending-card-visual { position: relative; overflow: hidden; background-size: cover; background-position: center; min-height: 180px; border: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; justify-content: flex-end; padding: 20px; transition: 0.3s; }
        .attending-card-visual:hover { transform: translateY(-5px); border-color: var(--accent-green); box-shadow: 0 5px 20px rgba(0,255,157,0.1); }
        .att-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to top, #000 0%, rgba(0,0,0,0.2) 100%); z-index: 1; }
        .att-content { position: relative; z-index: 2; }
        .att-title { font-family: 'Kanit'; font-size: 1.5rem; font-style: italic; line-height: 1.1; margin-bottom: 5px; text-transform: uppercase; color: white; }
        .att-organizer { display: block; color: var(--accent-green); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .att-meta { display: flex; gap: 15px; font-size: 0.8rem; color: #ddd; }
        .att-meta i { color: var(--accent-green); margin-right: 5px; }
        .workshop-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin-bottom: 40px; }
        .workshop-card { background: rgba(0,0,0,0.5); border: 1px solid #333; padding: 20px; position: relative; transition: 0.3s; }
        .workshop-card:hover { border-color: var(--accent-purple); box-shadow: 0 0 20px rgba(157, 78, 221, 0.15); }
        .ws-title { font-family: 'Kanit'; font-size: 1.2rem; margin-bottom: 15px; color: white; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .ws-img { width: 100%; height: auto; border: 1px solid #444; margin-bottom: 15px; opacity: 0.9; }
        .hsv-box { background: #111; padding: 10px; font-family: monospace; font-size: 0.8rem; color: #ccc; border-left: 3px solid var(--accent-green); }
        .hsv-line { margin-bottom: 5px; display: block; }
        .btn-neon-red { display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 0, 85, 0.1); border: 1px solid var(--accent-red); color: var(--accent-red); padding: 15px 30px; font-family: 'Kanit'; font-size: 1rem; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; }
        .btn-neon-red:hover { background: var(--accent-red); color: white; box-shadow: 0 0 20px var(--accent-red); }
        .btn-neon-yellow { display: inline-flex; align-items: center; gap: 10px; background: rgba(255, 215, 0, 0.1); border: 1px solid var(--accent-gold); color: var(--accent-gold); padding: 15px 30px; font-family: 'Kanit'; font-size: 1rem; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; pointer-events: none; }
        .form-add-event { background: rgba(255,255,255,0.02); padding: 30px; border: 1px solid #333; margin-bottom: 30px; }
        .input-event { background: #111; border: 1px solid #444; color: white; padding: 10px; width: 200px; }
        .btn-add { background: var(--accent-purple); color: white; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; text-transform: uppercase; }
        .discord-box { background: #2f3136; color: #dcddde; padding: 20px; border-radius: 5px; border-left: 5px solid #5865f2; font-family: monospace; white-space: pre-wrap; margin-top: 20px; }
        
        @media (max-width: 1024px) { .hero-section { grid-template-columns: 1fr; } .attending-grid { grid-template-columns: 1fr; } .workshop-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .sidebar { display: none; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">VINTARA <span>// HUB</span></div>
        <div class="lang-switcher-sidebar">
            <button onclick="cambiarIdioma('es')" class="lang-mini-btn <?php echo ($lang == 'es') ? 'active' : ''; ?>">ES</button>
            <button onclick="cambiarIdioma('en')" class="lang-mini-btn <?php echo ($lang == 'en') ? 'active' : ''; ?>">EN</button>
        </div>
        <div class="vtc-mini-widget">
            <img src="<?php echo $api_vtc_imagen; ?>" class="vtc-logo-img" alt="Logo">
            <div class="vtc-name"><?php echo $api_vtc_nombre; ?></div>
            <div class="vtc-stats">
                <div><b><?php echo $api_vtc_miembros; ?></b> <?php echo $t['nav_members']; ?></div>
                <div><b><?php echo $api_vtc_reclutamiento; ?></b> <?php echo $t['nav_recruiting']; ?></div>
            </div>
        </div>
        <?php if ($es_admin): ?>
        <div class="nav-category"><?php echo $t['nav_admin']; ?></div>
        <a href="?view=events&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'events') ? 'active' : ''; ?>"><i class="fas fa-calendar-plus"></i> <?php echo $t['nav_manage_events']; ?></a>
        <a href="?view=database&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'database') ? 'active' : ''; ?>"><i class="fas fa-database"></i> <?php echo $t['nav_users']; ?></a>
        <?php endif; ?>
        <div class="nav-category"><?php echo $t['nav_menu']; ?></div>
        <a href="?view=dashboard&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-satellite-dish"></i> <?php echo $t['nav_ops']; ?></a>
        <a href="?view=workshop&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'workshop') ? 'active' : ''; ?>"><i class="fas fa-tools"></i> <?php echo $t['nav_workshop']; ?></a>
        <a href="?view=tickets&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'tickets') ? 'active' : ''; ?>"><i class="fas fa-headset"></i> <?php echo $t['nav_tickets']; ?></a>
        <a href="?view=store&lang=<?php echo $lang; ?>" class="nav-link <?php echo ($vista == 'store') ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> <?php echo $t['nav_store']; ?></a>
        <div class="user-credential">
            <img src="<?php echo $mi_avatar; ?>" class="user-avatar" alt="User">
            <div class="user-info">
                <h4><?php echo $_SESSION['username']; ?></h4>
                <span class="user-rank"><?php echo $mi_rango_vtc; ?></span>
                <span class="user-id">TMP: <?php echo $mi_tmp_id; ?></span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-header">
            <div>
                <div style="font-family:'Kanit'; font-size:0.8rem; letter-spacing:2px; color:#444;">/// TERMINAL ID: <?php echo $mi_tmp_id; ?></div>
                <div class="page-title">
                    <?php 
                        if ($vista == 'events') echo $t['title_events'];
                        elseif ($vista == 'database') echo $t['title_users'];
                        elseif ($vista == 'workshop') echo $t['title_workshop'];
                        elseif ($vista == 'tickets') echo $t['title_tickets'];
                        elseif ($vista == 'store') echo $t['title_store'];
                        else echo $t['title_ops'];
                    ?>
                </div>
            </div>
            <a href="logout.php" class="logout-btn"><?php echo $t['logout']; ?></a>
        </div>

        <?php if ($vista == 'events' && $es_admin): ?>
            <div class="form-add-event">
                <h3 style="font-family:'Kanit'; margin-bottom:15px; color:white;">AGREGAR NUEVO EVENTO</h3>
                <form method="POST">
                    <label style="color:#888;">Event ID:</label>
                    <input type="number" name="add_event_id" class="input-event" placeholder="Ej: 12345" required>
                    <button type="submit" class="btn-add">Guardar</button>
                </form>
                <?php if($mensaje_accion) echo "<p style='margin-top:10px; color:var(--accent-green);'>$mensaje_accion</p>"; ?>
            </div>
            <?php if ($discord_template): ?><div style="margin-bottom:40px;"><h4 style="font-family:'Kanit'; color:var(--accent-purple);">PLANTILLA DISCORD:</h4><div class="discord-box"><?php echo $discord_template; ?></div></div><?php endif; ?>
            <div class="attending-section-title">Eventos Guardados</div>
            <?php foreach ($lista_eventos_db as $evt_db): ?>
                <div style="background:#111; padding:15px; border-left:3px solid #333; display:flex; justify-content:space-between; margin-bottom:5px;">
                    <span style="color:#666;">ID: <?php echo $evt_db['event_id']; ?></span>
                    <a href="?view=events&del_event=<?php echo $evt_db['event_id']; ?>&lang=<?php echo $lang; ?>" style="color:#ff0055;">ELIMINAR</a>
                </div>
            <?php endforeach; ?>

        <?php elseif ($vista == 'database' && $es_admin): ?>
            <?php include 'includes/admin_table_view.php'; ?>

        <?php elseif ($vista == 'workshop'): ?>
            <div class="attending-section-title"><?php echo $t['workshop_paint']; ?> <span>// Midnight Edge</span></div>
            <div class="workshop-grid">
                <div class="workshop-card">
                    <div class="ws-title"><?php echo $t['workshop_truck']; ?></div>
                    <img src="assets/img/truck.png" class="ws-img" alt="Truck">
                    <div class="hsv-box"><span class="hsv-line" style="color:#ff0055;">■ PINTURA: Midnight Edge</span></div>
                </div>
                <div class="workshop-card">
                    <div class="ws-title"><?php echo $t['workshop_trailer']; ?></div>
                    <img src="assets/img/trailer.png" class="ws-img" alt="Trailer">
                    <div class="hsv-box"><span class="hsv-line" style="color:#ff0055;">■ PINTURA: Midnight Edge</span></div>
                </div>
            </div>
            <div class="attending-section-title"><?php echo $t['workshop_tag']; ?> <span>// <?php echo $t['workshop_id']; ?></span></div>
            <div class="workshop-card">
                <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
                    <img src="assets/img/tag.png" style="max-width: 400px; border: 1px solid #444;" alt="Tag">
                    <div>
                        <div style="font-family: 'Kanit'; font-size: 2rem; color: #00ff9d; margin-bottom: 10px;">Vintara|Driver</div>
                        <div class="hsv-box">RGB: 255 | 255 | 255</div>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 50px;">
                <a href="" id="guideBtn" class="btn-neon-red" target="_blank"><i class="fab fa-youtube"></i> <?php echo $t['workshop_guide']; ?></a>
            </div>

        <?php elseif ($vista == 'tickets'): ?>
            <?php include 'includes/tickets_view.php'; ?>
        
        <?php elseif ($vista == 'store'): ?>
            <div style="display:flex; flex-direction:column; justify-content:center; align-items:center; height:60vh; text-align:center; color:white;">
                <i class="fas fa-hard-hat" style="font-size:5rem; color:#333; margin-bottom:20px; border: 2px dashed #9d00ff; padding:30px; border-radius:50%;"></i>
                <h1 style="font-family:'Kanit'; font-style:italic; font-size:3rem; margin:0; text-transform:uppercase;"><?php echo $t['store_construction']; ?></h1>
                <p style="color:#666; font-size:1.2rem; margin-top:10px;"><?php echo $t['store_soon']; ?></p>
            </div>

        <?php else: ?>
            <div class="hero-section">
                <div class="vtc-stat-card">
                    <img src="<?php echo $api_vtc_imagen; ?>" class="vtc-logo-big" alt="Logo">
                    <div class="vtc-big-number"><?php echo $api_vtc_miembros; ?></div>
                    <div style="color: #666; font-size: 0.8rem; letter-spacing: 1px; margin-top: 5px;"><?php echo $t['stats_total']; ?></div>
                    <div style="margin-top: 20px; padding: 5px 15px; background: rgba(0,255,157,0.1); border: 1px solid var(--accent-green); color: var(--accent-green); border-radius: 20px; font-size: 0.7rem; font-weight: bold;"><?php echo $t['stats_verified']; ?></div>
                </div>
                <?php if ($evento_destacado): ?>
                    <div class="featured-event-card" style="background-image: url('<?php echo $evento_destacado['banner']; ?>'); background-size: cover;">
                        <div class="event-overlay"></div>
                        <a href="<?php echo $evento_destacado['url']; ?>" target="_blank" class="btn-event">Ver en TMP <i class="fas fa-external-link-alt"></i></a>
                        <div class="event-content">
                            <span class="tag-hosted"><?php echo $t['event_official']; ?></span>
                            <h2 class="event-title"><?php echo $evento_destacado['nombre']; ?></h2>
                            <div class="event-details">
                                <div><i class="far fa-calendar-alt"></i> <?php echo date('d M', $evento_destacado['fecha']); ?></div>
                                <div><i class="far fa-clock"></i> <?php echo date('H:i', $evento_destacado['fecha']); ?> UTC</div>
                                <div><i class="fas fa-server"></i> <?php echo $evento_destacado['server']; ?></div>
                                <div><i class="fas fa-map-marker-alt"></i> <?php echo $evento_destacado['salida']; ?> <i class="fas fa-arrow-right"></i> <?php echo $evento_destacado['llegada']; ?></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="featured-event-card" style="align-items: center; justify-content: center; background: rgba(255,255,255,0.02);"><h3 style="font-family: 'Kanit'; color: #666;">Sin eventos propios</h3></div>
                <?php endif; ?>
            </div>
            <div class="attending-section-title"><?php echo $t['event_agenda']; ?> <span>// <?php echo $t['event_invited']; ?></span></div>
            <div class="attending-grid">
                <?php if (!empty($eventos_asistencia)): ?>
                    <?php foreach ($eventos_asistencia as $evt): ?>
                        <div class="attending-card-visual" style="background-image: url('<?php echo $evt['banner'] ? $evt['banner'] : 'assets/img/header.png'; ?>');">
                            <div class="att-overlay"></div>
                            <div class="att-content">
                                <span class="att-organizer">Organiza: <?php echo $evt['organiza']; ?></span>
                                <h3 class="att-title"><?php echo substr($evt['nombre'], 0, 30) . (strlen($evt['nombre']) > 30 ? '...' : ''); ?></h3>
                                <div class="att-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('d M - H:i', $evt['fecha']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo $evt['salida']; ?> <i class="fas fa-arrow-right"></i> <?php echo $evt['llegada']; ?></span>
                                </div>
                            </div>
                            <a href="<?php echo $evt['url']; ?>" target="_blank" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:3;"></a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; padding: 30px; color: #555; border: 1px dashed #333; text-align: center;"><?php echo $t['event_none']; ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const guideBtn = document.getElementById('guideBtn');
            if(guideBtn) {
                const href = guideBtn.getAttribute('href');
                if(!href || href === '#' || href.trim() === '') {
                    guideBtn.innerHTML = '<i class="fas fa-clock"></i> <?php echo $t['workshop_soon']; ?>';
                    guideBtn.className = 'btn-neon-yellow';
                    guideBtn.removeAttribute('href');
                }
            }
        });
    </script>
</body>
</html>