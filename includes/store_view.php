<!-- store_view.php -->
<?php

require_once __DIR__ . "/db.php";

// 1. DICCIONARIO DE TRADUCCIÓN PARA TICKETS
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($lang) ? $lang : 'es');

//puntos totales y disponibles
$stmt = $conn->prepare("SELECT total_points, available_points FROM user_stats WHERE user_id = ?");
$stmt->bind_param("i", $my_id);
$stmt->execute();
$stats_res = $stmt->get_result();
$user_data = $stats_res->fetch_assoc();

// Valores por defecto si el usuario no tiene registro de stats aún
$total_pts = $user_data['total_points'] ?? 0;
$available_pts = $user_data['available_points'] ?? 0;
$redeemed_pts = $total_pts - $available_pts;

// 2. Contar logros (obtenidos vs totales)
$total_ach_query = $conn->query("SELECT COUNT(*) as total FROM achievements WHERE active = 1");
$total_ach = $total_ach_query->fetch_assoc()['total'];

$earned_ach_query = $conn->prepare("SELECT COUNT(*) as earned FROM user_achievements WHERE user_id = ?");
$earned_ach_query->bind_param("i", $my_id);
$earned_ach_query->execute();
$earned_ach = $earned_ach_query->get_result()->fetch_assoc()['earned'];



if (!isset($conn)) {
    echo "<h3 style='color:red'><i class='fas fa-exclamation-triangle'></i> Error: Database connection lost.</h3>";
    exit;
}

$my_id = $_SESSION['user_id'];
$my_role = $_SESSION['role'];
?>

<style>
    .vintara-container {
        color: white;
        font-family: 'Kanit', sans-serif;
        padding: 20px;
    }

    .terminal-id {
        color: #9d00ff;
        font-size: 0.8rem;
        margin-bottom: 5px;
    }

    .main-title {
        font-size: 2.5rem;
        font-style: italic;
        text-transform: uppercase;
        margin-bottom: 25px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        padding: 15px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        border-left: 4px solid #fff;
    }

    .stat-card.purple {
        border-color: #9d00ff;
    }

    .stat-card.green {
        border-color: #00ff88;
    }

    .stat-card.blue {
        border-color: #0088ff;
    }

    .stat-card.gold {
        border-color: #ffcc00;
    }

    .stat-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.7rem;
        color: #aaa;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: bold;
        margin-top: 5px;
    }

    /* Tabs */
    .tabs-container {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
    }

    .tab-btn {
        background: #222;
        border: none;
        color: #888;
        padding: 10px 25px;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: bold;
    }

    .tab-btn.active {
        background: #9d00ff;
        color: white;
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Cards */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .achievement-card {
        background: rgba(0, 255, 136, 0.05);
        border: 1px solid rgba(0, 255, 136, 0.2);
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        position: relative;
    }

    .ach-icon {
        font-size: 1.5rem;
        color: #00ff88;
        margin-right: 15px;
    }

    .ach-info h4 {
        margin: 0;
        font-size: 1.1rem;
    }

    .ach-info p {
        margin: 5px 0;
        font-size: 0.8rem;
        color: #ccc;
    }

    .ach-info .pts {
        color: #00ff88;
        font-weight: bold;
    }

    .ach-date {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 0.7rem;
        color: #666;
    }

    /* Botones Recompensas */
    .btn-redeem {
        width: 100%;
        background: #9d00ff;
        border: none;
        color: white;
        padding: 8px;
        border-radius: 5px;
        margin-top: 10px;
        cursor: pointer;
        font-weight: bold;
    }

    .btn-redeem:disabled {
        background: #333;
        color: #666;
        cursor: not-allowed;
    }

    /* Tablas */
    .vintara-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.02);
    }

    .vintara-table th {
        text-align: left;
        padding: 15px;
        border-bottom: 1px solid #333;
        color: #9d00ff;
        font-size: 0.8rem;
    }

    .vintara-table td {
        padding: 15px;
        border-bottom: 1px solid #222;
    }

    .status-tag {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: bold;
    }

    .status-tag.delivered {
        background: rgba(0, 255, 136, 0.1);
        color: #00ff88;
    }

    .vintara-container {
        padding: 30px;
        background: transparent;
    }

    /* Stats Cards con Efecto Glow */
    .stats-grid {
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: rgba(15, 15, 15, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-left: none;
        /* Quitamos el borde sólido lateral */
        position: relative;
        overflow: hidden;
        padding: 20px;
        transition: transform 0.3s ease;
    }

    /* Línea de acento superior curva */
    .stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 40px;
        height: 100%;
        border-top: 3px solid var(--accent-color);
        border-left: 3px solid var(--accent-color);
        border-top-left-radius: 10px;
    }

    .stat-card.purple {
        --accent-color: #9d00ff;
    }

    .stat-card.green {
        --accent-color: #00ff88;
    }

    .stat-card.blue {
        --accent-color: #0088ff;
    }

    .stat-card.gold {
        --accent-color: #ffcc00;
    }

    .stat-value {
        font-size: 2.2rem;
        text-shadow: 0 0 15px var(--accent-color);
    }

    /* Estilo de los Botones (Tabs) */
    .tabs-container {
        background: rgba(255, 255, 255, 0.03);
        padding: 5px;
        border-radius: 8px;
        display: inline-flex;
    }

    .tab-btn {
        background: transparent;
        color: #aaa;
        padding: 12px 30px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Corregido: El estado active ahora brilla */
    .tab-btn.active {
        background: #9d00ff !important;
        color: white !important;
        box-shadow: 0 0 20px rgba(157, 0, 255, 0.4);
    }

    /* Tarjetas de Logros */
    .achievement-card {
        background: rgba(20, 20, 20, 0.8);
        border: 1px solid rgba(0, 255, 136, 0.15);
        padding: 20px;
        border-radius: 4px;
        /* Más cuadrado como el diseño */
        transition: 0.3s;
    }

    .achievement-card.unlocked {
        border-left: 4px solid #00ff88;
    }

    .achievement-card.pending {
        opacity: 0.5;
        filter: grayscale(1);
        border-left: 4px solid #444;
    }

    .ach-icon {
        width: 50px;
        height: 50px;
        background: rgba(0, 255, 136, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.2rem;
    }

    .section-subtitle {
        color: #eee;
        text-transform: uppercase;
        font-size: 1rem;
        margin: 30px 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="vintara-container">
    <div class="stats-grid">
        <div class="stat-card purple">
            <div class="stat-label"><span><?php echo $t['store_stat_total_pts']; ?></span> <i class="fas fa-trophy"></i></div>
            <div class="stat-value"><?php echo number_format($total_pts); ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label"><span><?php echo $t['store_stat_available']; ?></span> <i class="fas fa-star"></i></div>
            <div class="stat-value"><?php echo number_format($available_pts); ?></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label"><span><?php echo $t['store_stat_redeemed']; ?></span> <i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo number_format($redeemed_pts); ?></div>
        </div>
        <div class="stat-card gold">
            <div class="stat-label"><span><?php echo $t['store_stat_achievements']; ?></span> <i class="fas fa-medal"></i></div>
            <div class="stat-value"><?php echo $earned_ach . "/" . $total_ach; ?></div>
        </div>
    </div>

    <div class="tabs-container">
        <button class="tab-btn active" onclick="openTab(event, 'mis-logros')"><?php echo $t['store_tab_my_achievements']; ?></button>
        <button class="tab-btn" onclick="openTab(event, 'recompensas')"><?php echo $t['store_tab_rewards']; ?></button>
        <button class="tab-btn" onclick="openTab(event, 'historial')"><?php echo $t['store_tab_history']; ?></button>
    </div>

    <div id="mis-logros" class="tab-content active">
        <h3 class="section-subtitle"><i class="fas fa-check-circle" style="color:#00ff88"></i> <?php echo $t['store_ach_unlocked']; ?></h3>
        <div class="cards-grid">
            <?php
            $sql_earned = "SELECT a.*, ua.earned_at 
                       FROM achievements a 
                       JOIN user_achievements ua ON a.id = ua.achievement_id 
                       WHERE ua.user_id = ? AND a.active = 1";
            $stmt_e = $conn->prepare($sql_earned);
            $stmt_e->bind_param("i", $my_id);
            $stmt_e->execute();
            $res_e = $stmt_e->get_result();

            while ($ach = $res_e->fetch_assoc()): ?>
                <div class="achievement-card unlocked">
                    <div class="ach-icon"><i class="<?php echo $ach['icon']; ?>"></i></div>
                    <div class="ach-info">
                        <h4><?php echo $ach['name']; ?></h4>
                        <p><?php echo $ach['description']; ?></p>
                        <span class="pts">+<?php echo $ach['points_reward']; ?> pts</span>
                    </div>
                    <div class="ach-date"><?php echo date('Y-m-d', strtotime($ach['earned_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        </div>

        <h3 class="section-subtitle"><i class="fas fa-lock" style="color:#666"></i> <?php echo $t['store_ach_pending']; ?></h3>
        <div class="cards-grid">
            <?php
            $sql_pending = "SELECT * FROM achievements 
                        WHERE id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?) 
                        AND active = 1";
            $stmt_p = $conn->prepare($sql_pending);
            $stmt_p->bind_param("i", $my_id);
            $stmt_p->execute();
            $res_p = $stmt_p->get_result();

            while ($ach_p = $res_p->fetch_assoc()): ?>
                <div class="achievement-card pending">
                    <div class="ach-icon"><i class="<?php echo $ach_p['icon']; ?>"></i></div>
                    <div class="ach-info">
                        <h4><?php echo $ach_p['name']; ?></h4>
                        <p><?php echo $ach_p['description']; ?></p>
                        <span class="pts">+<?php echo $ach_p['points_reward']; ?> pts</span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="recompensas" class="tab-content">
        <div class="cards-grid">
            <?php
            $rewards_q = $conn->query("SELECT * FROM rewards WHERE active = 1 AND stock > 0");
            while ($rew = $rewards_q->fetch_assoc()):
                $can_afford = ($available_pts >= $rew['cost_points']);
            ?>
                <div class="reward-card <?php echo !$can_afford ? 'disabled' : ''; ?>">
                    <div class="reward-icon"><i class="fas fa-gift"></i></div>
                    <div class="reward-header">
                        <span class="price"><?php echo $rew['cost_points']; ?> pts</span>
                    </div>
                    <h4><?php echo $rew['name']; ?></h4>
                    <p><?php echo $rew['description']; ?></p>
                    <span class="stock"><?php echo $rew['stock']; ?> <?php echo $t['store_rew_stock']; ?></span>

                    <?php if ($can_afford): ?>
                        <form action="actions/redeem_reward.php" method="POST">
                            <input type="hidden" name="reward_id" value="<?php echo $rew['id']; ?>">
                            <button type="submit" class="btn-redeem"><?php echo $t['store_rew_redeem']; ?></button>
                        </form>
                    <?php else: ?>
                        <button class="btn-redeem" disabled><?php echo $t['store_rew_insufficient']; ?></button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div id="historial" class="tab-content">
        <table class="vintara-table">
            <thead>
                <tr>
                    <th><?php echo $t['store_hist_col_date']; ?></th>
                    <th><?php echo $t['store_hist_col_reward']; ?></th>
                    <th><?php echo $t['store_hist_col_points']; ?></th>
                    <th><?php echo $t['store_hist_col_status']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para obtener el historial del usuario logueado
                $sql_hist = "SELECT ur.*, r.name as reward_name
                            FROM user_rewards ur
                            JOIN rewards r ON ur.reward_id = r.id
                            WHERE ur.user_id = ?
                            ORDER BY ur.created_at DESC";
                $stmt_h = $conn->prepare($sql_hist);
                $stmt_h->bind_param("i", $my_id);
                $stmt_h->execute();
                $res_h = $stmt_h->get_result();

                if ($res_h->num_rows > 0):
                    while ($row = $res_h->fetch_assoc()):
                        // Mapeo de estados para las clases CSS y traducciones
                        $status_class = '';
                        $status_text = '';
                        switch ($row['status']) {
                            case 'entregado':
                                $status_class = 'delivered';
                                $status_text = $t['store_hist_status_delivered'];
                                break;
                            case 'pendiente':
                                $status_class = 'pending';
                                $status_text = $t['store_hist_status_pending'];
                                break;
                            case 'cancelado':
                                $status_class = 'cancelled'; // Asegúrate de definir esta clase si la usas
                                $status_text = 'CANCELADO';
                                break;
                        }
                ?>
                        <tr>
                            <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                            <td><?php echo $row['reward_name']; ?></td>
                            <td style="color:#9d00ff"><?php echo number_format($row['points_spent']); ?> pts</td>
                            <td><span class="status-tag <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        </tr>
                    <?php
                    endwhile;
                else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:20px; color:#666;">
                            No tienes canjes registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function openTab(evt, tabName) {
        // 1. Ocultar todos los contenidos
        const tabcontents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabcontents.length; i++) {
            tabcontents[i].style.display = "none";
            tabcontents[i].classList.remove("active");
        }

        // 2. Quitar clase 'active' de TODOS los botones
        const tablinks = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }

        // 3. Mostrar el tab actual y añadir clase active al botón
        const targetTab = document.getElementById(tabName);
        if (targetTab) {
            targetTab.style.display = "block";
            targetTab.classList.add("active");
        }

        evt.currentTarget.classList.add("active");
    }
</script>