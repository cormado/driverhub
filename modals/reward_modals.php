<style>
    /* --- ESTILOS PARA EL MODAL ADMIN --- */

    .vintara-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        /* Fondo oscuro detrás */
        z-index: 10000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(6px);
        /* Desenfoque de fondo */
    }

    .modal-content {
        background: #11111d;
        /* Fondo azul marino muy oscuro */
        border: 1px solid #9d00ff;
        /* Borde púrpura distintivo */
        width: 90%;
        max-width: 600px;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 40px rgba(157, 0, 255, 0.25);
        color: white;
        font-family: 'Kanit', sans-serif;
    }

    .modal-scrollable {
        max-height: 85vh;
        overflow-y: auto;
        padding-right: 10px;
    }


    .modal-content h2 {
        margin-top: 0;
        font-style: italic;
        text-transform: uppercase;
        font-size: 1.8rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    /* --- ESTILOS DE FORMULARIO DENTRO DEL MODAL --- */

    .vintara-form .form-group {
        margin-bottom: 20px;
    }

    .vintara-form label {
        display: block;
        color: #9d00ff;
        /* Etiquetas color púrpura */
        font-size: 0.85rem;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .vintara-form input[type="text"],
    .vintara-form input[type="number"],
    .vintara-form textarea,
    .vintara-form select {
        width: 100%;
        background: #1a1a25;
        /* Fondo del input */
        border: 1px solid #333;
        color: #fff;
        padding: 12px 15px;
        border-radius: 6px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-sizing: border-box;
        /* Evita que el padding ensanche el input */
    }

    .vintara-form input:focus,
    .vintara-form textarea:focus {
        border-color: #9d00ff;
        outline: none;
        background: #1e1e2d;
        box-shadow: 0 0 10px rgba(157, 0, 255, 0.1);
    }

    .vintara-form textarea {
        resize: none;
    }

    /* Filas dobles (Puntos y Categoría) */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    /* --- BOTONES DEL MODAL --- */

    .modal-footer {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-modal-save {
        background: #9d00ff;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 1rem;
        text-transform: uppercase;
        cursor: pointer;
        flex: 2;
        /* El botón de guardar es más ancho */
        transition: background 0.3s;
    }

    .btn-modal-save:hover {
        background: #7a00cc;
    }

    .btn-modal-cancel {
        background: #30363d;
        color: #ccc;
        border: none;
        padding: 14px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 1rem;
        text-transform: uppercase;
        cursor: pointer;
        flex: 1;
        transition: all 0.3s;
    }

    .btn-modal-cancel:hover {
        background: #444;
        color: white;
    }

    /* Checkbox estilizado */
    .vintara-form input[type="checkbox"] {
        accent-color: #9d00ff;
        width: 18px;
        height: 18px;
        vertical-align: middle;
        margin-right: 10px;
    }
</style>

<div id="modalLogro" class="vintara-modal">
    <div class="modal-content modal-scrollable">
        <h2 id="modalLogroTitle"><?php echo $t['store_admin_btn_create_logro']; ?></h2>
        <form class="vintara-form" action="actions/manage_achievements.php" method="POST">
            <input type="hidden" name="action" id="logroAction" value="create">
            <input type="hidden" name="id" id="logroId">

            <div class="form-group">
                <label><?php echo $t['store_admin_col_codigo']; ?></label>
                <input type="text" name="code" id="logroCode" placeholder="EJ: SAFE_DRIVER_100" required>
            </div>

            <div class="form-group">
                <label><?php echo $t['store_admin_col_nombre']; ?></label>
                <input type="text" name="name" id="logroName" placeholder="Conductor Seguro" required>
            </div>

            <div class="form-group">
                <label><?php echo $t['store_admin_col_descripcion']; ?></label>
                <textarea name="description" id="logroDesc" rows="3" placeholder="Descripción del logro..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><?php echo $t['store_admin_col_puntos']; ?></label>
                    <input type="number" name="points" id="logroPoints" min="0" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['store_admin_col_categoria']; ?></label>
                    <input type="text" name="category" id="logroCategory" placeholder="Seguridad">
                </div>
            </div>

            <div class="form-group">
                <label>Icon (FontAwesome Class)</label>
                <input type="text" name="icon" id="logroIcon" placeholder="fas fa-shield-alt">
                <small style="color: #666; font-size: 0.7rem;">Ejemplo: fas fa-trophy, fas fa-star</small>
            </div>

            <hr>
            <h3><?php echo $t['store_admin_modal_rules']; ?></h3>

            <div class="form-group">
                <label><?php echo $t['store_admin_modal_tipe_achievement']; ?></label>
                <select name="rule_type" id="logroRuleType" required>
                    <option value="distance_job"><?php echo $t['store_admin_modal_min_distance']; ?></option>
                    <option value="total_jobs"><?php echo $t['store_admin_modal_required_jobs']; ?></option>
                    <option value="distance_total"><?php echo $t['store_admin_modal_type_distance_acumulated']; ?></option>
                </select>
            </div>

            <div class="form-row">

                <div class="form-group" id="groupMinDistance">
                    <label><?php echo $t['store_admin_modal_distance_in_km']; ?></label>
                    <input type="number" name="min_distance_km" id="logroMinDistance" min="0">
                </div>

                <div class="form-group" id="groupRequiredJobs">
                    <label><?php echo $t['store_admin_modal_trips_required']; ?></label>
                    <input type="number" name="required_jobs" id="logroRequiredJobs" min="0">
                </div>

                <div class="form-group" id="groupTotalKm">
                    <label><?php echo $t['store_admin_modal_total_distance_required']; ?></label>
                    <input type="number" name="required_total_km" id="logroRequiredTotalKm" min="0">
                </div>

            </div>


            <div class="form-group">
                <label>
                    <input type="checkbox" name="active" id="logroActive" value="1" checked style="width: auto;">
                    <?php echo $t['store_admin_lbl_activo']; ?>
                </label>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-modal-save"><?php echo $t['store_admin_lbl_guardar']; ?></button>
                <button type="button" class="btn-modal-cancel" onclick="toggleModal('modalLogro', false)"><?php echo $t['store_admin_lbl_cancelar']; ?></button>
            </div>
        </form>
    </div>
</div>

<div id="modalRecompensa" class="vintara-modal">
    <div class="modal-content">
        <h2 id="modalRewardTitle"><?php echo $t['store_admin_btn_create_recompensa']; ?></h2>
        <form class="vintara-form" action="actions/manage_rewards.php" method="POST">
            <input type="hidden" name="action" id="rewardAction" value="create">
            <input type="hidden" name="id" id="rewardId">

            <div class="form-group">
                <label><?php echo $t['store_admin_col_codigo']; ?></label>
                <input type="text" name="code" id="rewardCode" placeholder="GIFT_CARD_50" required>
            </div>

            <div class="form-group">
                <label><?php echo $t['store_admin_col_nombre']; ?></label>
                <input type="text" name="name" id="rewardName" placeholder="Tarjeta de Regalo $50" required>
            </div>

            <div class="form-group">
                <label><?php echo $t['store_admin_col_descripcion']; ?></label>
                <textarea name="description" id="rewardDesc" rows="3" placeholder="Descripción de la recompensa..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><?php echo $t['store_admin_col_costo']; ?> (Pts)</label>
                    <input type="number" name="cost" id="rewardCost" min="0" required>
                </div>
                <div class="form-group">
                    <label><?php echo $t['store_admin_col_stock']; ?></label>
                    <input type="number" name="stock" id="rewardStock" min="0" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="active" id="rewardActive" value="1" checked style="width: auto;">
                    <?php echo $t['store_admin_lbl_activo']; ?>
                </label>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="infinite_stock" id="rewardInfiniteStock" value="1" style="width:auto;">
                        Stock infinito
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-modal-save"><?php echo $t['store_admin_lbl_guardar']; ?></button>
                <button type="button" class="btn-modal-cancel" onclick="toggleModal('modalRecompensa', false)"><?php echo $t['store_admin_lbl_cancelar']; ?></button>
            </div>
        </form>
    </div>
</div>


<script>
    /**
     * Lógica para abrir modal de Recompensa en modo edición
     * @param {Object} data - Objeto con la información de la recompensa de la BD
     */
    function openEditRewardModal(data) {
        document.getElementById('rewardAction').value = 'edit';
        document.getElementById('modalRewardTitle').innerText = 'EDITAR RECOMPENSA';
        document.getElementById('rewardId').value = data.id;
        document.getElementById('rewardCode').value = data.code;
        document.getElementById('rewardName').value = data.name;
        document.getElementById('rewardDesc').value = data.description;
        document.getElementById('rewardCost').value = data.cost_points;
        document.getElementById('rewardStock').value = data.stock;
        document.getElementById('rewardActive').checked = parseInt(data.active) === 1;
        document.getElementById('rewardInfiniteStock').checked = parseInt(data.infinite_stock) === 1;
        document.getElementById('rewardStock').disabled = parseInt(data.infinite_stock) === 1;

        toggleModal('modalRecompensa', true);
    }
    document.getElementById('rewardInfiniteStock').addEventListener('change', function() {
        document.getElementById('rewardStock').disabled = this.checked;
        document.getElementById('rewardStock').value = 0;
    });

    function openCreateRewardModal() {
        document.getElementById('rewardAction').value = 'create';
        document.getElementById('modalRewardTitle').innerText = 'CREAR RECOMPENSA';
        document.getElementById('rewardId').value = '';
        document.getElementById('rewardCode').value = '';
        document.getElementById('rewardName').value = '';
        document.getElementById('rewardDesc').value = '';
        document.getElementById('rewardCost').value = '';
        document.getElementById('rewardStock').value = '';
        document.getElementById('rewardActive').checked = true;
        document.getElementById('rewardInfiniteStock').checked = false;
        document.getElementById('rewardStock').disabled = false;

        toggleModal('modalRecompensa', true);
    }

    /**
     * Actualiza los campos visibles en el modal de Logro según el tipo de regla seleccionado
     * @param {string} type - Tipo de regla seleccionado
     */
    function updateRuleFields(type) {

        const groupMinDistance = document.getElementById('groupMinDistance');
        const groupJobs = document.getElementById('groupRequiredJobs');
        const groupTotalKm = document.getElementById('groupTotalKm');

        const minDistance = document.getElementById('logroMinDistance');
        const jobs = document.getElementById('logroRequiredJobs');
        const totalKm = document.getElementById('logroRequiredTotalKm');

        // Ocultar todo
        groupMinDistance.style.display = 'none';
        groupJobs.style.display = 'none';
        groupTotalKm.style.display = 'none';

        // Limpiar valores para que PHP reciba NULL
        minDistance.value = '';
        jobs.value = '';
        totalKm.value = '';

        if (type === 'distance_job') {
            groupMinDistance.style.display = 'block';
            groupJobs.style.display = 'block';
        }

        if (type === 'total_jobs') {
            groupJobs.style.display = 'block';
        }

        if (type === 'distance_total') {
            groupTotalKm.style.display = 'block';
        }
    }

    // Evento para cambiar campos según tipo de regla
    document.getElementById('logroRuleType').addEventListener('change', function() {
        updateRuleFields(this.value);
    });

    /**
     * Abre el modal de Logro en modo edición
     * @param {Object} data - Objeto con la información del logro de la BD
     */
    function openEditLogroModal(data) {

        document.getElementById('logroAction').value = 'edit';
        document.getElementById('modalLogroTitle').innerText = 'EDITAR LOGRO';

        document.getElementById('logroId').value = data.id;
        document.getElementById('logroCode').value = data.code;
        document.getElementById('logroName').value = data.name;
        document.getElementById('logroDesc').value = data.description;
        document.getElementById('logroPoints').value = data.points_reward;
        document.getElementById('logroCategory').value = data.category || '';
        document.getElementById('logroIcon').value = data.icon || '';
        document.getElementById('logroActive').checked = parseInt(data.active) === 1;

        document.getElementById('logroRuleType').value = data.rule_type;

        // Mostrar campos correctos
        updateRuleFields(data.rule_type);

        // Cargar valores reales
        if (data.min_distance_km !== null) {
            document.getElementById('logroMinDistance').value = data.min_distance_km;
        }

        if (data.required_jobs !== null) {
            document.getElementById('logroRequiredJobs').value = data.required_jobs;
        }

        if (data.required_total_km !== null) {
            document.getElementById('logroRequiredTotalKm').value = data.required_total_km;
        }

        toggleModal('modalLogro', true);
    }
</script>