<?php
// Usamos el idioma definido o detectado
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($lang) ? $lang : 'es');



if (!isset($conn)) {
    echo "<h3 style='color:red'><i class='fas fa-exclamation-triangle'></i> Error: Database connection lost.</h3>";
    exit;
}
?>

<style>
    .vintara-admin-container {
        color: white;
        font-family: 'Kanit', sans-serif;
        padding: 20px;
    }

    .terminal-id {
        color: #9d00ff;
        font-size: 0.8rem;
        margin-bottom: 5px;
        opacity: 0.8;
    }

    .main-title {
        font-size: 2.5rem;
        font-style: italic;
        text-transform: uppercase;
        margin-bottom: 25px;
    }

    /* Tabs */
    .admin-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
    }

    .tab-btn {
        background: #1a1a25;
        border: none;
        color: #888;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: bold;
        text-transform: uppercase;
    }

    .tab-btn.active {
        background: #9d00ff;
        color: white;
        box-shadow: 0 0 15px rgba(157, 0, 255, 0.4);
    }

    /* Secciones */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3 {
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        font-size: 1.2rem;
    }

    .btn-create {
        background: #9d00ff;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Tablas Estilo Imagen */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(15, 15, 25, 0.5);
        border-radius: 8px;
        overflow: hidden;
    }

    .admin-table th {
        text-align: left;
        padding: 18px 15px;
        border-bottom: 2px solid #222;
        color: #9d00ff;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .admin-table td {
        padding: 15px;
        border-bottom: 1px solid #1a1a1a;
        font-size: 0.9rem;
        vertical-align: middle;
    }

    .txt-purple {
        color: #9d00ff;
        font-weight: bold;
    }

    /* Botones de acción */
    .action-btns {
        display: flex;
        gap: 15px;
    }

    .btn-icon {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.2s;
    }

    .btn-edit {
        color: #0088ff;
    }

    .btn-delete {
        color: #ff4444;
    }

    /* Modales */
    .vintara-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: #11111d;
        border: 1px solid #9d00ff;
        width: 90%;
        max-width: 550px;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 30px rgba(157, 0, 255, 0.2);
    }

    .modal-content h2 {
        font-style: italic;
        margin-top: 0;
        text-transform: uppercase;
        border-bottom: 1px solid #222;
        padding-bottom: 10px;
    }

    /* Status Tags */
    .status-tag {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: bold;
    }

    .status-tag.active {
        color: #00ff88;
        border: 1px solid #00ff88;
        background: rgba(0, 255, 136, 0.05);
    }

    .status-tag.inactive {
        color: #ff4444;
        border: 1px solid #ff4444;
        background: rgba(255, 68, 68, 0.05);
    }

    /* Alineación de botones de acción */
    .action-btns {
        display: flex;
        align-items: center;
        /* Centra verticalmente */
        gap: 10px;
        height: 100%;
    }

    .action-btns form {
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        margin: 0;
        line-height: 1;
    }

    /* Estilo para el tag de estado basado en tus imágenes */
    .status-tag {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: bold;
        display: inline-block;
        text-align: center;
        min-width: 70px;
    }

    .status-tag.active {
        border: 1px solid #00ff88;
        color: #00ff88;
        background: rgba(0, 255, 136, 0.1);
    }

    .status-tag.inactive {
        border: 1px solid #ff4444;
        color: #ff4444;
        background: rgba(255, 68, 68, 0.1);
    }
</style>

<div class="vintara-admin-container">
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="openAdminTab(event, 'gest-logros')"><?php echo $t['store_admin_tab_logros']; ?></button>
        <button class="tab-btn" onclick="openAdminTab(event, 'gest-recompensas')"><?php echo $t['store_admin_tab_recompensas']; ?></button>
    </div>

    <div id="gest-logros" class="tab-content" style="display: block;">
        <div class="section-header">
            <h3>GESTIONAR LOGROS</h3>
            <button class="btn-create" onclick="toggleModal('modalLogro', true)">
                <i class="fas fa-plus"></i> <?php echo $t['store_admin_btn_create_logro']; ?>
            </button>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?php echo $t['store_admin_col_codigo']; ?></th>
                    <th><?php echo $t['store_admin_col_nombre']; ?></th>
                    <th><?php echo $t['store_admin_col_categoria']; ?></th>
                    <th><?php echo $t['store_admin_col_puntos']; ?></th>
                    <th><?php echo $t['store_admin_col_estado']; ?></th>
                    <th><?php echo $t['store_admin_col_usuarios']; ?></th>
                    <th><?php echo $t['store_admin_col_acciones']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resLogros = $conn->query("SELECT a.*, (SELECT COUNT(*) FROM user_achievements ua WHERE ua.achievement_id = a.id) as total_users FROM achievements a ORDER BY id DESC");
                while ($logro = $resLogros->fetch_assoc()):
                    $dataLogro = htmlspecialchars(json_encode($logro), ENT_QUOTES, 'UTF-8');
                ?>
                    <tr>
                        <td><?php echo $logro['code']; ?></td>
                        <td>
                            <i class="<?php echo $logro['icon']; ?> txt-purple" style="margin-right: 8px;"></i>
                            <?php echo $logro['name']; ?>
                        </td>
                        <td><?php echo $logro['category']; ?></td>
                        <td class="txt-purple"><?php echo $logro['points_reward']; ?></td>
                        <td>
                            <span class="status-tag <?php echo $logro['active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $logro['active'] ? 'ACTIVO' : 'INACTIVO'; ?>
                            </span>
                        </td>
                        <td><i class="fas fa-users" style="font-size: 0.8rem;"></i> <?php echo $logro['total_users']; ?></td>
                        <td class="action-btns">
                            <button class="btn-icon btn-edit" onclick='openEditLogroModal(<?php echo $dataLogro; ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <form action="actions/manage_achievements.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este logro?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $logro['id']; ?>">
                                <button type="submit" class="btn-icon btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="gest-recompensas" class="tab-content" style="display: none;">
        <div class="section-header">
            <h3>GESTIONAR RECOMPENSAS</h3>
            <button class="btn-create" onclick="toggleModal('modalRecompensa', true)">
                <i class="fas fa-plus"></i> <?php echo $t['store_admin_btn_create_recompensa']; ?>
            </button>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><?php echo $t['store_admin_col_codigo']; ?></th>
                    <th><?php echo $t['store_admin_col_nombre']; ?></th>
                    <th><?php echo $t['store_admin_col_costo']; ?></th>
                    <th><?php echo $t['store_admin_col_stock']; ?></th>
                    <th><?php echo $t['store_admin_col_estado']; ?></th>
                    <th><?php echo $t['store_admin_col_canjes']; ?></th>
                    <th><?php echo $t['store_admin_col_acciones']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $resRewards = $conn->query("SELECT r.*, (SELECT COUNT(*) FROM user_rewards ur WHERE ur.reward_id = r.id) as total_canjes FROM rewards r ORDER BY id DESC");
                while ($rew = $resRewards->fetch_assoc()):
                    // Preparamos los datos para el JS
                    $dataJson = htmlspecialchars(json_encode($rew), ENT_QUOTES, 'UTF-8');
                ?>
                    <tr>
                        <td><?php echo $rew['code']; ?></td>
                        <td><?php echo $rew['name']; ?></td>
                        <td class="txt-purple"><?php echo $rew['cost_points']; ?> pts</td>
                        <td><?php echo $rew['stock']; ?></td>
                        <td>
                            <span class="status-tag <?php echo $rew['active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $rew['active'] ? 'ACTIVO' : 'INACTIVO'; ?>
                            </span>
                        </td>
                        <td><i class="fas fa-user-friends"></i> <?php echo $rew['total_canjes']; ?></td>
                        <td class="action-btns">
                            <button class="btn-icon btn-edit" onclick='openEditRewardModal(<?php echo $dataJson; ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <form action="actions/manage_rewards.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta recompensa?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $rew['id']; ?>">
                                <button type="submit" class="btn-icon btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'modals/reward_modals.php'; ?>

<script>
    function openAdminTab(evt, tabName) {
        const contents = document.querySelectorAll(".tab-content");
        contents.forEach(c => c.style.display = "none");
        const buttons = document.querySelectorAll(".tab-btn");
        buttons.forEach(b => b.classList.remove("active"));
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("active");
    }

    function toggleModal(id, show) {
        const modal = document.getElementById(id);
        if (modal) modal.style.display = show ? 'flex' : 'none';
    }
</script>

<script>
    // Función para abrir el modal en modo CREAR (Limpia los campos)
    function openCreateRewardModal() {
        document.getElementById('rewardAction').value = 'create';
        document.getElementById('modalRewardTitle').innerText = 'CREAR RECOMPENSA';
        document.getElementById('rewardId').value = '';

        // Limpiar los inputs (ajusta los IDs según tu reward_modals.php)
        document.getElementById('rewardCode').value = '';
        document.getElementById('rewardName').value = '';
        document.getElementById('rewardDesc').value = '';
        document.getElementById('rewardCost').value = '';
        document.getElementById('rewardStock').value = '';
        document.getElementById('rewardActive').checked = true;

        toggleModal('modalRecompensa', true);
    }

    // Función para abrir el modal en modo EDITAR (Rellena los campos)
    function openEditRewardModal(data) {
        // Cambiamos el comportamiento del formulario
        document.getElementById('rewardAction').value = 'edit';
        document.getElementById('modalRewardTitle').innerText = 'EDITAR RECOMPENSA';

        // Rellenamos los campos con el objeto 'data' que viene de la tabla
        document.getElementById('rewardId').value = data.id;
        document.getElementById('rewardCode').value = data.code;
        document.getElementById('rewardName').value = data.name;
        document.getElementById('rewardDesc').value = data.description;
        document.getElementById('rewardCost').value = data.cost_points;
        document.getElementById('rewardStock').value = data.stock;

        // El checkbox (data.active suele venir como "1" o "0")
        document.getElementById('rewardActive').checked = (data.active == 1);

        toggleModal('modalRecompensa', true);
    }

    function openCreateLogroModal() {
        document.getElementById('logroAction').value = 'create';
        document.getElementById('modalLogroTitle').innerText = 'CREAR LOGRO';
        document.getElementById('logroId').value = '';
        document.getElementById('logroCode').value = '';
        document.getElementById('logroName').value = '';
        document.getElementById('logroDesc').value = '';
        document.getElementById('logroPoints').value = '';
        document.getElementById('logroCategory').value = 'Seguridad';
        document.getElementById('logroIcon').value = 'fas fa-shield-alt';
        document.getElementById('logroActive').checked = true;
        toggleModal('modalLogro', true);
    }
</script>