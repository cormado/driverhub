<?php
session_start();
require 'includes/db.php';
require 'includes/api.php';

require_once 'includes/i18n.php';

// SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?lang=$lang");
    exit();
}

$rol_actual = $_SESSION['role'];
if ($rol_actual !== 'admin' && $rol_actual !== 'owner') {
    header("Location: dashboard.php?lang=$lang");
    exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tmp_id = intval($_POST['tmp_id']);
    
    // --- CAMBIO 1: LOGICA PARA QUE SEA OPCIONAL (NULL SI ESTA VACIO) ---
    // Si envian algo, lo convertimos a numero. Si no, lo dejamos como NULL.
    $trucky_id = !empty($_POST['trucky_id']) ? intval($_POST['trucky_id']) : null;
    
    $password = $_POST['password'];
    $system_role = $_POST['role'];

    if ($system_role === 'admin' && $rol_actual !== 'owner') {
        $mensaje = $t['create_msg_denied'];
    } elseif (!empty($tmp_id) && !empty($password)) {

        $datos_jugador = conectarTruckersMP("/player/$tmp_id");

        if (isset($datos_jugador['response'])) {
            $p = $datos_jugador['response'];
            $username = $p['name'];
            $avatar = $p['avatar'];

            $rango_vtc = ($lang == 'es') ? "Conductor" : "Driver";

            if (isset($p['vtc']['id']) && $p['vtc']['id'] == 81636) {
                $lista_miembros = conectarTruckersMP("/vtc/81636/members");
                if (isset($lista_miembros['response']['members'])) {
                    foreach ($lista_miembros['response']['members'] as $miembro) {
                        if ($miembro['user_id'] == $tmp_id) {
                            $rango_vtc = $miembro['role'];
                            break;
                        }
                    }
                }
            } elseif (isset($p['vtc']['name'])) {
                $rango_vtc = $p['vtc']['name'];
            }

            $check = $conn->query("SELECT id FROM users WHERE tmp_id = '$tmp_id' OR username = '$username'");
            if ($check->num_rows > 0) {
                $mensaje = $t['create_msg_exists'];
            } else {
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Preparamos la inserción
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, tmp_id, avatar_url, vtc_rank, trucky_driver_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                // Nota: 'sssissi' está bien. Si $trucky_id es null, mysqli lo maneja correctamente.
                $stmt->bind_param("sssissi", $username, $pass_hash, $system_role, $tmp_id, $avatar, $rango_vtc, $trucky_id);

                if ($stmt->execute()) {
                    header("Location: dashboard.php?view=database&lang=$lang");
                    exit();
                } else {
                    $mensaje = $t['create_msg_db_error'] . " " . $conn->error;
                }
            }
        } else {
            $mensaje = sprintf($t['create_msg_not_found'], $tmp_id);
        }
    } else {
        $mensaje = $t['create_msg_required'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $t['create_page_title']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,300;0,600;1,800&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #050505;
            color: white;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.9)), url('assets/img/header.png');
            background-size: cover;
        }

        .glass-card {
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid #333;
            padding: 40px;
            width: 400px;
            border-top: 3px solid #00ff9d;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        }

        .title {
            font-family: 'Kanit';
            font-size: 1.8rem;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .label {
            display: block;
            color: #00ff9d;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-family: 'Kanit';
            letter-spacing: 1px;
        }

        .input {
            width: 100%;
            padding: 10px;
            background: #000;
            border: 1px solid #444;
            color: white;
            font-family: 'Montserrat';
            box-sizing: border-box; /* Agregado para que el padding no rompa el ancho */
        }

        .input:focus {
            border-color: #00ff9d;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #00ff9d;
            color: black;
            border: none;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            font-family: 'Kanit';
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn:hover {
            background: white;
            transform: scale(1.02);
        }

        .msg {
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.8rem;
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid red;
            color: red;
        }
    </style>
</head>

<body>
    <div class="glass-card">
        <h2 class="title"><?php echo $t['create_title_1']; ?> <span><?php echo $t['create_title_2']; ?></span></h2>

        <?php if ($mensaje): ?><div class="msg"><?php echo $mensaje; ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="label"><?php echo $t['create_label_tmp']; ?></label>
                <input type="number" name="tmp_id" class="input" placeholder="Ej: 123456" required>
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['create_label_trucky']; ?> (Opcional)</label>
                <input type="number" name="trucky_id" class="input" placeholder="Dejar vacío si no tiene">
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['create_label_pass']; ?></label>
                <input type="text" name="password" class="input" required>
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['create_label_role']; ?></label>
                <select name="role" class="input">
                    <option value="conductor"><?php echo $t['create_role_driver']; ?></option>

                    <?php if ($rol_actual === 'owner'): ?>
                        <option value="admin" style="color: #9d4edd; font-weight:bold;"><?php echo $t['create_role_admin']; ?></option>
                    <?php endif; ?>

                </select>
                <?php if ($rol_actual !== 'owner'): ?>
                    <small style="color:#666; font-size:0.65rem;"><?php echo $t['create_owner_only']; ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn"><?php echo $t['create_btn_submit']; ?></button>
            <a href="dashboard.php?lang=<?php echo $lang; ?>" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none; font-size:0.8rem;"><?php echo $t['create_btn_cancel']; ?></a>
        </form>
    </div>
</body>

</html>