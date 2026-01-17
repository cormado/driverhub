<?php
// includes/admin_table_view.php
if (!isset($conn)) {
    require 'db.php';
}



// LÓGICA PARA PROCESAR EL BANEO
if (isset($_POST['apply_ban'])) {
    $uid = (int)$_POST['ban_user_id'];
    $reason = $conn->real_escape_string($_POST['reason']);
    $duration = (int)$_POST['duration'];

    $banned_until = date('Y-m-d H:i:s', strtotime("+$duration days"));
    $conn->query("UPDATE users SET banned_until = '$banned_until', ban_reason = '$reason' WHERE id = $uid");
    echo "<script>window.location='dashboard.php?view=database&banned=success&lang=$lang';</script>";
}

// LÓGICA DE BÚSQUEDA
$busqueda = "";
$where_clause = "";
if (isset($_GET['q'])) {
    $busqueda = $conn->real_escape_string($_GET['q']);
    $where_clause = "WHERE username LIKE '%$busqueda%' OR tmp_id LIKE '%$busqueda%'";
}

$sql_users = "SELECT * FROM users $where_clause ORDER BY id DESC";
$res_users = $conn->query($sql_users);
?>

<style>
    .modal-ban {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10px);
    }

    .modal-ban-content {
        background: #111;
        margin: 10% auto;
        padding: 30px;
        border: 1px solid var(--accent-purple);
        width: 90%;
        max-width: 400px;
        border-radius: 5px;
        box-shadow: 0 0 30px rgba(157, 78, 221, 0.2);
    }

    .ban-input {
        width: 100%;
        background: #000;
        border: 1px solid #333;
        color: white;
        padding: 12px;
        margin: 10px 0;
        font-family: 'Montserrat';
    }

    .ban-input:focus {
        border-color: var(--accent-purple);
        outline: none;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <div style="font-family: 'Kanit'; color: #666; font-size: 0.9rem;">
        <?php echo ($busqueda) ? $t['admin_table_results'] : $t['admin_table_total']; ?>
        <span style="color: white;"><?php echo $res_users->num_rows; ?></span>
    </div>

    <form method="GET" action="dashboard.php" style="display: flex; gap: 5px;">
        <input type="hidden" name="view" value="database">
        <input type="hidden" name="lang" value="<?php echo $lang; ?>">
        <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="<?php echo $t['admin_table_search_ph']; ?>"
            style="background: #111; border: 1px solid #444; color: white; padding: 8px 15px; font-family: 'Montserrat'; width: 250px;">
        <button type="submit" style="background: var(--accent-purple); color: white; border: none; padding: 8px 15px; cursor: pointer;">
            <i class="fas fa-search"></i>
        </button>
        <?php if ($busqueda): ?>
            <a href="dashboard.php?view=database&lang=<?php echo $lang; ?>" style="background: #333; color: #ccc; text-decoration: none; padding: 8px 15px; display: flex; align-items: center;">
                <i class="fas fa-times"></i>
            </a>
        <?php endif; ?>
    </form>

    <a href="admin_create.php?lang=<?php echo $lang; ?>" class="btn-add" style="text-decoration:none; display:inline-block; font-size:0.8rem; padding: 8px 15px; border: 1px solid var(--accent-green); color: var(--accent-green);">
        <i class="fas fa-user-plus"></i> <?php echo $t['admin_table_btn_new']; ?>
    </a>
</div>

<div style="overflow-x: auto;">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--accent-purple);">
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_id']; ?></th>
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_agent']; ?></th>
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_tmp']; ?></th>
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_trucky']; ?></th>
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_role']; ?></th>
                <th style="text-align: left; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_status']; ?></th>
                <th style="text-align: right; padding: 15px; color: var(--accent-purple); font-family: 'Kanit';"><?php echo $t['admin_table_th_actions']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res_users->num_rows > 0): ?>
                <?php while ($row = $res_users->fetch_assoc()): ?>
                    <?php
                    $avatar = !empty($row['avatar_url']) ? $row['avatar_url'] : 'assets/img/logo.png';
                    $is_banned = ($row['banned_until'] != NULL);
                    ?>
                    <tr style="border-bottom: 1px solid #222; <?php echo $is_banned ? 'background: rgba(255,0,0,0.05);' : ''; ?>">
                        <td style="padding: 15px; color: #666;">#<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td style="padding: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo $avatar; ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #444;">
                                <span style="font-weight: 600; color: white; font-family: 'Montserrat';"><?php echo $row['username']; ?></span>
                            </div>
                        </td>
                        <td style="padding: 15px; font-family: monospace; color: #aaa;"><?php echo $row['tmp_id']; ?></td>
                        <td style="padding: 15px; font-family: monospace; color: #aaa;"><?php echo $row['trucky_driver_id']; ?></td>
                        <td style="padding: 15px; font-family: monospace; color: #aaa;"><?php echo $row['role']; ?></td>
                        <td style="padding: 15px;">
                            <?php if ($is_banned): ?>
                                <span style="color: #ff0055; font-size: 0.8rem; font-weight: bold;"><i class="fas fa-ban"></i> <?php echo $t['admin_table_status_banned']; ?></span>
                            <?php else: ?>
                                <span style="color: #00ff88; font-size: 0.8rem;"><i class="fas fa-check-circle"></i> <?php echo $t['admin_table_status_active']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px; text-align: right;">
                            <a href="admin_edit.php?id=<?php echo $row['id']; ?>&lang=<?php echo $lang; ?>" class="btn-action" title="<?php echo $t['admin_table_title_edit']; ?>"><i class="fas fa-pen"></i></a>

                            <button onclick="openBanModal(<?php echo $row['id']; ?>, '<?php echo $row['username']; ?>')"
                                style="background:none; border:none; color:#ffaa00; cursor:pointer; margin-left:10px; font-size:1rem;" title="<?php echo $t['admin_table_title_ban']; ?>">
                                <i class="fas fa-gavel"></i>
                            </button>

                            <a href="dashboard.php?view=database&del_user=<?php echo $row['id']; ?>&lang=<?php echo $lang; ?>" onclick="return confirm('<?php echo $t['admin_table_confirm_del']; ?>');" style="color: #ff0055; opacity: 0.7; text-decoration: none; font-size: 0.9rem; margin-left: 10px;" title="<?php echo $t['admin_table_title_delete']; ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="banModal" class="modal-ban">
    <div class="modal-ban-content">
        <h2 style="font-family: 'Kanit'; color: white; margin-bottom: 20px; font-style: italic;"><?php echo $t['admin_table_modal_title']; ?></h2>
        <p style="color: #aaa; font-size: 0.9rem;"><?php echo $t['admin_table_modal_user']; ?> <b id="banUserName" style="color: var(--accent-purple);"></b></p>

        <form method="POST">
            <input type="hidden" name="ban_user_id" id="banUserId">

            <label style="font-size: 0.8rem; color: #666; text-transform: uppercase;"><?php echo $t['admin_table_modal_reason']; ?></label>
            <textarea name="reason" class="ban-input" rows="3" placeholder="<?php echo $t['admin_table_modal_reason_ph']; ?>" required></textarea>

            <label style="font-size: 0.8rem; color: #666; text-transform: uppercase;"><?php echo $t['admin_table_modal_duration']; ?></label>
            <input type="number" name="duration" class="ban-input" value="7" min="1" required>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="apply_ban" style="flex: 1; background: var(--accent-purple); color: white; border: none; padding: 12px; cursor: pointer; font-family: 'Kanit'; font-weight: bold;"><?php echo $t['admin_table_btn_apply']; ?></button>
                <button type="button" onclick="closeBanModal()" style="flex: 1; background: #222; color: #fff; border: none; padding: 12px; cursor: pointer;"><?php echo $t['admin_table_btn_cancel']; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBanModal(id, name) {
        document.getElementById('banUserId').value = id;
        document.getElementById('banUserName').innerText = name;
        document.getElementById('banModal').style.display = 'block';
    }

    function closeBanModal() {
        document.getElementById('banModal').style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target == document.getElementById('banModal')) {
            closeBanModal();
        }
    }
</script>