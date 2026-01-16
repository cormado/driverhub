<?php
session_start();
error_reporting(0);
require 'includes/db.php';
require 'includes/api.php'; // Aquí se cargan $evento_destacado y $eventos_asistencia

// --- 1. IDIOMA ---
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$trans = [
    'es' => [
        'nav_admin' => 'Admin',
        'nav_manage_events' => 'Gestionar Eventos',
        'nav_users' => 'Usuarios',
        'nav_store_manage' => 'Gestión de Tienda',
        'nav_menu' => 'Menú',
        'nav_ops' => 'Centro de Operaciones',
        'nav_workshop' => 'Taller Vintara',
        'nav_tickets' => 'Soporte / Tickets',
        'nav_store' => 'Tienda',
        'nav_members' => 'Miembros',
        'nav_recruiting' => 'Recluta',
        'logout' => 'Cerrar Sesión',
        'title_ops' => 'CENTRO DE OPERACIONES',
        'title_events' => 'GESTOR DE EVENTOS',
        'title_users' => 'GESTIÓN DE USUARIOS',
        'title_store_maange' => 'GESTIÓN DE TIENDA',
        'title_workshop' => 'TALLER OFICIAL',
        'title_tickets' => 'SOPORTE',
        'title_store' => 'TIENDA',
        'stats_total' => 'MIEMBROS TOTALES',
        'stats_verified' => 'VERIFICADA',
        'event_official' => '★ PRÓXIMO EVENTO OFICIAL',
        'event_agenda' => 'Agenda de Asistencia',
        'event_invited' => 'Convoys Invitados',
        'event_none' => 'No hay eventos en la agenda.',
        'workshop_paint' => 'Pintura Oficial',
        'workshop_truck' => 'CAMIÓN',
        'workshop_trailer' => 'REMOLQUE',
        'workshop_tag' => 'Tag Oficial',
        'workshop_id' => 'Identificación',
        'workshop_guide' => 'GUIA DEL CONDUCTOR',
        'workshop_soon' => 'GUIA: PROXIMAMENTE',
        'store_construction' => 'En Construcción',
        'store_soon' => 'El módulo de Tienda Vintara estará disponible pronto.',
        'ban_denied' => 'ACCESO DENEGADO',
        'ban_suspended' => 'CUENTA SUSPENDIDA',
        'ban_reason' => 'Motivo:',
        'ban_release' => 'Liberación:'
    ],
    'en' => [
        'nav_admin' => 'Admin',
        'nav_manage_events' => 'Manage Events',
        'nav_users' => 'Users',
        'nav_store_manage' => 'Manage Store',
        'nav_menu' => 'Menu',
        'nav_ops' => 'Operations Center',
        'nav_workshop' => 'Vintara Workshop',
        'nav_tickets' => 'Support / Tickets',
        'nav_store' => 'Store',
        'nav_members' => 'Members',
        'nav_recruiting' => 'Recruiting',
        'logout' => 'Logout',
        'title_ops' => 'OPERATIONS CENTER',
        'title_events' => 'EVENT MANAGER',
        'title_users' => 'USER MANAGEMENT',
        'title_store_maange' => 'STORE MANAGEMENT',
        'title_workshop' => 'OFFICIAL WORKSHOP',
        'title_tickets' => 'SUPPORT',
        'title_store' => 'STORE',
        'stats_total' => 'TOTAL MEMBERS',
        'stats_verified' => 'VERIFIED',
        'event_official' => '★ NEXT OFFICIAL EVENT',
        'event_agenda' => 'Attendance Agenda',
        'event_invited' => 'Guest Convoys',
        'event_none' => 'No events in the agenda.',
        'workshop_paint' => 'Official Paintjob',
        'workshop_truck' => 'TRUCK',
        'workshop_trailer' => 'TRAILER',
        'workshop_tag' => 'Official Tag',
        'workshop_id' => 'Identification',
        'workshop_guide' => 'DRIVER GUIDE',
        'workshop_soon' => 'GUIDE: COMING SOON',
        'store_construction' => 'Under Construction',
        'store_soon' => 'Vintara Store module will be available soon.',
        'ban_denied' => 'ACCESS DENIED',
        'ban_suspended' => 'ACCOUNT SUSPENDID',
        'ban_reason' => 'Reason:',
        'ban_release' => 'Release:'
    ]
];
if (!array_key_exists($lang, $trans)) {
    $lang = 'en';
}
$t = $trans[$lang];

// 2. SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

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
        if ($sigue_en_vtc) {
            $conn->query("UPDATE users SET banned_until = NULL, ban_reason = NULL WHERE id = $uid_check");
        } else {
            $conn->query("DELETE FROM users WHERE id = $uid_check");
            session_destroy();
            header("Location: index.php?msg=expulsado&lang=$lang");
            exit();
        }
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
            if ($check['role'] === 'owner') {
                header("Location: dashboard.php?view=database&lang=$lang");
                exit();
            }
        }
        $conn->query("DELETE FROM users WHERE id = $id_a_borrar");
    }
    header("Location: dashboard.php?view=database&lang=$lang");
    exit();
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
        } else {
            $mensaje_accion = "⚠️ Ya registrado.";
        }
    } else {
        $mensaje_accion = "❌ Error ID.";
    }
}
if ($es_admin && isset($_GET['del_event'])) {
    $conn->query("DELETE FROM event_ids WHERE event_id = " . intval($_GET['del_event']));
    header("Location: dashboard.php?view=events&lang=$lang");
    exit();
}
$lista_eventos_db = [];
if ($es_admin) {
    $res = $conn->query("SELECT * FROM event_ids ORDER BY id DESC");
    while ($row = $res->fetch_assoc()) {
        $lista_eventos_db[] = $row;
    }
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
    <link rel="stylesheet" href="css/dashboard.css">
    <script>
        function cambiarIdioma(nuevoLang) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', nuevoLang);
            window.location.href = url.toString();
        }
    </script>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-header">
            <div>
                <div style="font-family:'Kanit'; font-size:0.8rem; letter-spacing:2px; color:#444;">/// TERMINAL ID: <?php echo $mi_tmp_id; ?></div>
                <div class="page-title">
                    <?php
                    if ($vista == 'events') echo $t['title_events'];
                    elseif ($vista == 'database') echo $t['title_users'];
                    elseif ($vista == 'manageStore') echo $t['title_store_maange'];
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
            <?php include 'includes/events_view.php'; ?>

        <?php elseif ($vista == 'database' && $es_admin): ?>
            <?php include 'includes/admin_table_view.php'; ?>

        <?php elseif ($vista == 'manageStore' && $es_admin): ?>
            <?php include 'includes/admin_store_view.php'; ?>

        <?php elseif ($vista == 'workshop'): ?>
            <?php include 'includes/workshop_view.php'; ?>

        <?php elseif ($vista == 'tickets'): ?>
            <?php include 'includes/tickets_view.php'; ?>

        <?php elseif ($vista == 'store'): ?>
            <?php include 'includes/store_view.php'; ?>

        <?php else: ?>
            <?php include 'includes/operation_center_view.php' ?>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const guideBtn = document.getElementById('guideBtn');
            if (guideBtn) {
                const href = guideBtn.getAttribute('href');
                if (!href || href === '#' || href.trim() === '') {
                    guideBtn.innerHTML = '<i class="fas fa-clock"></i> <?php echo $t['workshop_soon']; ?>';
                    guideBtn.className = 'btn-neon-yellow';
                    guideBtn.removeAttribute('href');
                }
            }
        });
    </script>
</body>

</html>