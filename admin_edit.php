<?php
session_start();
require 'includes/db.php';

require_once 'includes/i18n.php';

// 2. SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?lang=$lang");
    exit();
}

$rol_actual = $_SESSION['role'];
if ($rol_actual !== 'admin' && $rol_actual !== 'owner') {
    header("Location: dashboard.php?lang=$lang");
    exit();
}

// 3. OBTENER DATOS
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?view=database&lang=$lang");
    exit();
}
$id_usuario = intval($_GET['id']);

$sql = "SELECT * FROM users WHERE id = $id_usuario";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: dashboard.php?view=database&lang=$lang");
    exit();
}
$usuario = $result->fetch_assoc();

$mensaje = "";

// 4. PROCESAR CAMBIOS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_tmp_id = intval($_POST['tmp_id']);
    
    // --- CAMBIO 1: LOGICA PARA QUE SEA OPCIONAL (NULL SI ESTA VACIO) ---
    $nuevo_trucky_id = !empty($_POST['trucky_id']) ? intval($_POST['trucky_id']) : null;
    
    $nuevo_pass = $_POST['password'];
    $nuevo_rol = $_POST['role'];

    if ($usuario['role'] === 'owner' && $rol_actual !== 'owner') {
        $mensaje = $t['edit_msg_owner_edit'];
    } elseif ($nuevo_rol === 'admin' && $rol_actual !== 'owner') {
        $mensaje = $t['edit_msg_admin_assign'];
    } else {
        // Preparamos la actualización
        $stmt = $conn->prepare("UPDATE users SET role = ?, tmp_id = ?, trucky_driver_id = ? WHERE id = ?");
        // Nota: Si $nuevo_trucky_id es null, MySQLi lo maneja correctamente aunque el tipo sea 'i'
        $stmt->bind_param("siii", $nuevo_rol, $nuevo_tmp_id, $nuevo_trucky_id, $id_usuario);
        $stmt->execute();       

        if (!empty($nuevo_pass)) {
            $pass_hash = password_hash($nuevo_pass, PASSWORD_DEFAULT);
            $stmt_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_pass->bind_param("si", $pass_hash, $id_usuario);
            $stmt_pass->execute();
        }

        header("Location: dashboard.php?view=database&lang=$lang");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <title><?php echo $t['edit_page_title']; ?></title>
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
            border-top: 3px solid #ffc107;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        }

        .title {
            font-family: 'Kanit';
            font-size: 1.8rem;
            text-transform: uppercase;
            margin-bottom: 5px;
            text-align: center;
        }

        .subtitle {
            font-size: 0.8rem;
            color: #888;
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Kanit';
        }

        .user-preview {
            text-align: center;
            margin-bottom: 20px;
        }

        .avatar-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 2px solid #ffc107;
            object-fit: cover;
        }

        .name-preview {
            display: block;
            font-weight: bold;
            margin-top: 10px;
            font-size: 1.2rem;
        }

        .rank-preview {
            color: #ffc107;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .label {
            display: block;
            color: #ffc107;
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
        }

        .input:focus {
            border-color: #ffc107;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #ffc107;
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
        <h2 class="title"><?php echo $t['edit_title_1']; ?> <span><?php echo $t['edit_title_2']; ?></span></h2>
        <div class="subtitle"><?php echo $t['edit_subtitle']; ?></div>

        <?php if ($mensaje): ?><div class="msg"><?php echo $mensaje; ?></div><?php endif; ?>

        <div class="user-preview">
            <img src="<?php echo $usuario['avatar_url'] ? $usuario['avatar_url'] : 'assets/img/logo.png'; ?>" class="avatar-preview">
            <span class="name-preview"><?php echo $usuario['username']; ?></span>
            <span class="rank-preview"><?php echo $usuario['vtc_rank']; ?></span>
        </div>

        <form method="POST">
            <div class="form-group">
                <label class="label"><?php echo $t['edit_label_tmp']; ?></label>
                <input type="number" name="tmp_id" class="input" value="<?php echo $usuario['tmp_id']; ?>" required>
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['create_label_trucky']; ?> (Opcional)</label>
                <input type="number" name="trucky_id" class="input" value="<?php echo $usuario['trucky_driver_id']; ?>" placeholder="Borrar número para quitar ID">
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['edit_label_pass']; ?></label>
                <input type="text" name="password" class="input" placeholder="<?php echo $t['edit_pass_ph']; ?>">
            </div>

            <div class="form-group">
                <label class="label"><?php echo $t['edit_label_role']; ?></label>
                <select name="role" class="input">
                    <?php if ($usuario['role'] == 'owner'): ?>
                        <option value="owner" selected><?php echo $t['edit_role_owner']; ?></option>
                    <?php else: ?>
                        <option value="conductor" <?php echo ($usuario['role'] == 'conductor') ? 'selected' : ''; ?>><?php echo $t['edit_role_driver']; ?></option>
                        <?php if ($rol_actual === 'owner'): ?>
                            <option value="admin" <?php echo ($usuario['role'] == 'admin') ? 'selected' : ''; ?>><?php echo $t['edit_role_admin']; ?></option>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn"><?php echo $t['edit_btn_submit']; ?></button>
            <a href="dashboard.php?view=database&lang=<?php echo $lang; ?>" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none; font-size:0.8rem;"><?php echo $t['edit_btn_cancel']; ?></a>
        </form>
    </div>
</body>

</html>