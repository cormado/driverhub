<?php
// includes/tickets_view.php

// 1. DICCIONARIO DE TRADUCCIÓN PARA TICKETS
$lang_code = isset($_GET['lang']) ? $_GET['lang'] : 'es';

$trans_ticket = [
    'es' => [
        'title_list' => 'MIS TICKETS',
        'btn_new' => 'NUEVO TICKET',
        'no_tickets' => 'No hay tickets recientes.',
        'back' => 'VOLVER',
        'author' => 'Autor:',
        'btn_close' => 'CERRAR TICKET',
        'btn_archive' => 'ARCHIVAR',
        'btn_delete' => 'BORRAR',
        'status_open' => 'ABIERTO',
        'status_closed' => 'RESUELTO',
        'status_process' => 'EN PROCESO',
        'placeholder_reply' => 'Escribe tu respuesta...',
        'locked' => 'TICKET CERRADO',
        'modal_new_title' => 'NUEVO TICKET',
        'modal_subject' => 'Asunto',
        'modal_desc' => 'Detalles...',
        'modal_create' => 'CREAR TICKET',
        'modal_del_title' => 'ELIMINAR TICKET',
        'modal_del_confirm' => '¿Estás seguro de que deseas eliminar este ticket permanentemente?',
        'modal_del_warn' => 'Esta acción no se puede deshacer.',
        'btn_cancel' => 'CANCELAR',
        'btn_confirm' => 'SI, BORRAR',
        'loading' => 'PROCESANDO...'
    ],
    'en' => [
        'title_list' => 'MY TICKETS',
        'btn_new' => 'NEW TICKET',
        'no_tickets' => 'No recent tickets.',
        'back' => 'BACK',
        'author' => 'Author:',
        'btn_close' => 'CLOSE TICKET',
        'btn_archive' => 'ARCHIVE',
        'btn_delete' => 'DELETE',
        'status_open' => 'OPEN',
        'status_closed' => 'RESOLVED',
        'status_process' => 'IN PROGRESS',
        'placeholder_reply' => 'Write your reply...',
        'locked' => 'TICKET CLOSED',
        'modal_new_title' => 'NEW TICKET',
        'modal_subject' => 'Subject',
        'modal_desc' => 'Details...',
        'modal_create' => 'CREATE TICKET',
        'modal_del_title' => 'DELETE TICKET',
        'modal_del_confirm' => 'Are you sure you want to delete this ticket permanently?',
        'modal_del_warn' => 'This action cannot be undone.',
        'btn_cancel' => 'CANCEL',
        'btn_confirm' => 'YES, DELETE',
        'loading' => 'PROCESSING...'
    ]
];

if (!isset($trans_ticket[$lang_code])) $lang_code = 'en';
$tt = $trans_ticket[$lang_code];

// 2. VERIFICACIÓN DE CONEXIÓN
if (!isset($conn)) {
    echo "<h3 style='color:red'><i class='fas fa-exclamation-triangle'></i> Error: Database connection lost.</h3>";
    exit;
}

$my_id = $_SESSION['user_id'];
$my_role = $_SESSION['role'];
?>

<style>
    /* Estilos exclusivos del Ticket System */
    .ticket-system {
        font-family: 'Montserrat', sans-serif;
        color: white;
        width: 100%;
    }

    .sys-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    .sys-modal-box {
        width: 90%;
        max-width: 500px;
        background: #050505;
        border: 1px solid #333;
        padding: 30px;
        position: relative;
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
    }

    .modal-danger {
        border: 2px solid #ff0055;
        box-shadow: 0 0 30px rgba(255, 0, 85, 0.2);
    }

    .modal-danger h3 {
        color: #ff0055;
    }

    .ticket-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 255, 255, 0.03);
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        margin-bottom: 15px;
        transition: 0.3s;
    }

    .ticket-row:hover {
        border-color: #9d4edd;
        background: rgba(157, 78, 221, 0.05);
    }

    .status-badge {
        padding: 5px 10px;
        font-size: 0.7rem;
        font-family: 'Kanit';
        text-transform: uppercase;
        border: 1px solid;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .st-open {
        color: #00ff9d;
        border-color: #00ff9d;
    }

    .st-closed {
        color: #ff0055;
        border-color: #ff0055;
    }

    .st-process {
        color: #ffd700;
        border-color: #ffd700;
    }

    .chat-box {
        height: 500px;
        background: rgba(0, 0, 0, 0.5);
        border: 1px solid #333;
        display: flex;
        flex-direction: column;
    }

    .chat-msgs {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .msg {
        padding: 15px;
        border-radius: 5px;
        max-width: 80%;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .msg-me {
        align-self: flex-end;
        background: rgba(0, 255, 157, 0.1);
        border: 1px solid #00ff9d;
        color: white;
    }

    .msg-other {
        align-self: flex-start;
        background: #111;
        border: 1px solid #444;
        color: #ccc;
    }

    .chat-input-area {
        padding: 20px;
        background: #111;
        border-top: 1px solid #333;
        display: flex;
        gap: 10px;
    }

    .chat-input {
        flex: 1;
        background: #000;
        border: 1px solid #444;
        color: white;
        padding: 10px;
        resize: none;
        height: 50px;
    }

    .btn-create {
        background: rgba(0, 255, 157, 0.1);
        border: 1px solid #00ff9d;
        color: #00ff9d;
        padding: 10px 20px;
        cursor: pointer;
        font-family: 'Kanit';
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-create:hover {
        background: #00ff9d;
        color: black;
    }

    .btn-action {
        cursor: pointer;
        padding: 8px 15px;
        border: none;
        font-family: 'Kanit';
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-cancel {
        background: #333;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-family: 'Kanit';
        transition: 0.2s;
    }

    .btn-cancel:hover {
        background: #555;
    }

    .btn-danger-confirm {
        background: #ff0055;
        color: white;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        font-family: 'Kanit';
        transition: 0.2s;
    }

    .btn-danger-confirm:hover {
        background: #ff4477;
        box-shadow: 0 0 15px #ff0055;
    }

    .loader {
        width: 30px;
        height: 30px;
        border: 3px solid #333;
        border-top: 3px solid #00ff9d;
        border-radius: 50%;
        animation: spin 1s infinite linear;
        margin: 0 auto;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div class="ticket-system">

    <?php
    if (isset($_GET['id']) && is_numeric($_GET['id'])):
        $t_id = intval($_GET['id']);
        $sql_t = "SELECT t.*, u.username as author_name FROM tickets t JOIN users u ON t.author_id = u.id WHERE t.id = $t_id";
        $res_ticket = $conn->query($sql_t);

        if (!$res_ticket || $res_ticket->num_rows == 0) {
            echo "<div style='padding:40px; text-align:center;'><i class='fas fa-times-circle' style='font-size:2rem; margin-bottom:10px;'></i><br>Ticket not found. <a href='?view=tickets&lang=$lang_code' style='color:#00ff9d'>{$tt['back']}</a></div>";
        } else {
            $ticket = $res_ticket->fetch_assoc();
            $msgs = $conn->query("SELECT tm.*, u.username FROM ticket_messages tm JOIN users u ON tm.user_id = u.id WHERE tm.ticket_id = $t_id ORDER BY tm.created_at ASC");
    ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <a href="?view=tickets&lang=<?php echo $lang_code; ?>" style="color:#888; text-decoration:none;"><i class="fas fa-arrow-left"></i> <?php echo $tt['back']; ?></a>
                <div style="display:flex; gap:10px;">
                    <?php if ($ticket['status'] != 'resuelto' && $ticket['status'] != 'archived'): ?>
                        <button onclick="accionTicket(<?php echo $t_id; ?>, 'close')" class="btn-action" style="background:rgba(255,0,85,0.2); color:#ff0055; border:1px solid #ff0055;">
                            <i class="fas fa-check"></i> <?php echo $tt['btn_close']; ?>
                        </button>
                    <?php endif; ?>
                    <?php if ($ticket['status'] == 'resuelto'): ?>
                        <button onclick="accionTicket(<?php echo $t_id; ?>, 'archive')" class="btn-action" style="background:#333;">
                            <i class="fas fa-archive"></i> <?php echo $tt['btn_archive']; ?>
                        </button>
                        <button onclick="confirmarBorrado(<?php echo $t_id; ?>)" class="btn-action" style="background:#ff0055;">
                            <i class="fas fa-trash"></i> <?php echo $tt['btn_delete']; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chat-box">
                <div style="padding:20px; border-bottom:1px solid #333; background:rgba(255,255,255,0.02);">
                    <h2 style="font-family:'Kanit'; margin:0; color:white;">
                        <i class="fas fa-hashtag" style="color:#444; font-size:0.8em;"></i><?php echo $ticket['id']; ?> <?php echo $ticket['subject']; ?>
                    </h2>
                    <div style="font-size:0.8rem; color:#888; margin-top:5px;">
                        <i class="fas fa-user"></i> <?php echo $tt['author']; ?> <span style="color:#9d4edd"><?php echo $ticket['author_name']; ?></span>
                    </div>
                </div>
                <div class="chat-msgs" id="chatBox">
                    <?php while ($m = $msgs->fetch_assoc()): $es_mio = ($m['user_id'] == $my_id); ?>
                        <div class="msg <?php echo $es_mio ? 'msg-me' : 'msg-other'; ?>">
                            <div style="font-size:0.7rem; color:#888; margin-bottom:5px;">
                                <?php echo $m['username']; ?> <i class="fas fa-circle" style="font-size:3px; vertical-align:middle; margin:0 5px;"></i> <?php echo date('H:i', strtotime($m['created_at'])); ?>
                            </div>
                            <?php echo nl2br($m['message']); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php if ($ticket['status'] != 'resuelto' && $ticket['status'] != 'archived'): ?>
                    <form id="chatForm" class="chat-input-area">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="ticket_id" value="<?php echo $t_id; ?>">
                        <textarea name="message" class="chat-input" placeholder="<?php echo $tt['placeholder_reply']; ?>" required></textarea>
                        <button type="submit" style="width:50px; background:#00ff9d; border:none; cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
                    </form>
                <?php else: ?>
                    <div style="padding:15px; text-align:center; background:rgba(255,0,0,0.2); color:#ff0055;">
                        <i class="fas fa-lock"></i> <?php echo $tt['locked']; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php }
    else:
        $where = ($my_role == 'admin' || $my_role == 'owner') ? "WHERE status != 'archived'" : "WHERE author_id = $my_id AND status != 'archived'";
        $sql = "SELECT * FROM tickets $where ORDER BY created_at DESC";
        $lista = $conn->query($sql);
        ?>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="font-family:'Kanit'; font-style:italic;"><?php echo $tt['title_list']; ?></h2>
            <button onclick="document.getElementById('modalNuevo').style.display='flex'" class="btn-create">
                <i class="fas fa-plus"></i> <?php echo $tt['btn_new']; ?>
            </button>
        </div>
        <div>
            <?php if ($lista && $lista->num_rows > 0): ?>
                <?php while ($row = $lista->fetch_assoc()): ?>
                    <a href="?view=tickets&id=<?php echo $row['id']; ?>&lang=<?php echo $lang_code; ?>" style="text-decoration:none;">
                        <div class="ticket-row">
                            <div>
                                <div style="font-family:'Kanit'; font-size:1.2rem; color:white;">
                                    <span style="color:#444; margin-right:5px;">#</span><?php echo $row['id']; ?> <?php echo $row['subject']; ?>
                                </div>
                                <div style="font-size:0.8rem; color:#888;">
                                    <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                            <div>
                                <?php
                                $raw_st = strtolower($row['status']);
                                if ($raw_st == 'abierto') {
                                    $st_txt = $tt['status_open'];
                                    $cls = 'st-open';
                                    $icon_st = 'fa-envelope-open';
                                } elseif ($raw_st == 'resuelto') {
                                    $st_txt = $tt['status_closed'];
                                    $cls = 'st-closed';
                                    $icon_st = 'fa-check-circle';
                                } else {
                                    $st_txt = $tt['status_process'];
                                    $cls = 'st-process';
                                    $icon_st = 'fa-spinner';
                                }
                                echo "<span class='status-badge $cls'><i class='fas $icon_st'></i> $st_txt</span>";
                                ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding:40px; text-align:center; border:1px dashed #333; color:#666;">
                    <i class="far fa-folder-open" style="font-size:2rem; margin-bottom:10px;"></i><br>
                    <?php echo $tt['no_tickets']; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<div id="modalNuevo" class="sys-modal">
    <div class="sys-modal-box">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h3 style="font-family:'Kanit'; margin:0; color:white;"><?php echo $tt['modal_new_title']; ?></h3>
            <span onclick="document.getElementById('modalNuevo').style.display='none'" style="cursor:pointer; color:#888;"><i class="fas fa-times"></i></span>
        </div>
        <form id="formNuevo">
            <input type="hidden" name="action" value="create">
            <input type="text" name="subject" class="chat-input" style="width:100%; margin-bottom:15px;" placeholder="<?php echo $tt['modal_subject']; ?>" required>
            <textarea name="message" class="chat-input" style="width:100%; height:100px; margin-bottom:15px;" placeholder="<?php echo $tt['modal_desc']; ?>" required></textarea>
            <button type="submit" class="btn-create" style="width:100%; justify-content:center;"><?php echo $tt['modal_create']; ?></button>
        </form>
    </div>
</div>

<div id="modalBorrar" class="sys-modal">
    <div class="sys-modal-box modal-danger">
        <h3 style="font-family:'Kanit'; margin-top:0;"><i class="fas fa-exclamation-triangle"></i> <?php echo $tt['modal_del_title']; ?></h3>
        <p style="color:#ddd; font-size:0.9rem; line-height:1.5;">
            <?php echo $tt['modal_del_confirm']; ?> <br>
            <span style="color:#666; font-size:0.8rem;"><?php echo $tt['modal_del_warn']; ?></span>
        </p>
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:25px;">
            <button onclick="cerrarModalBorrar()" class="btn-cancel"><?php echo $tt['btn_cancel']; ?></button>
            <button id="btnConfirmarBorrado" class="btn-danger-confirm"><?php echo $tt['btn_confirm']; ?></button>
        </div>
    </div>
</div>

<div id="modalCarga" class="sys-modal">
    <div class="sys-modal-box" style="text-align:center;">
        <h3 style="color:white; font-family:'Kanit';"><?php echo $tt['loading']; ?></h3>
        <div class="loader"></div>
    </div>
</div>

<script>
    let ticketIdToDelete = null;

    // AJAX: Crear Ticket
    const formNuevo = document.getElementById('formNuevo');
    if (formNuevo) {
        formNuevo.addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('modalCarga').style.display = 'flex';
            fetch('includes/api_tickets.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.text())
                .then(id => {
                    if (!isNaN(id)) window.location.href = '?view=tickets&id=' + id + '&lang=<?php echo $lang_code; ?>';
                    else location.reload();
                });
        });
    }

    // AJAX: Chat
    const chatForm = document.getElementById('chatForm');
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            btn.disabled = true;
            fetch('includes/api_tickets.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(() => location.reload());
        });
        const box = document.getElementById('chatBox');
        if (box) box.scrollTop = box.scrollHeight;
    }

    function accionTicket(id, accion) {
        document.getElementById('modalCarga').style.display = 'flex';
        const data = new FormData();
        data.append('action', accion);
        data.append('ticket_id', id);
        fetch('includes/api_tickets.php', {
                method: 'POST',
                body: data
            })
            .then(() => location.reload());
    }

    function confirmarBorrado(id) {
        ticketIdToDelete = id;
        document.getElementById('modalBorrar').style.display = 'flex';
    }

    function cerrarModalBorrar() {
        document.getElementById('modalBorrar').style.display = 'none';
        ticketIdToDelete = null;
    }

    document.getElementById('btnConfirmarBorrado').addEventListener('click', function() {
        if (ticketIdToDelete) {
            document.getElementById('modalBorrar').style.display = 'none';
            document.getElementById('modalCarga').style.display = 'flex';

            const data = new FormData();
            data.append('action', 'delete');
            data.append('ticket_id', ticketIdToDelete);

            fetch('includes/api_tickets.php', {
                    method: 'POST',
                    body: data
                })
                .then(() => {
                    window.location.href = '?view=tickets&lang=<?php echo $lang_code; ?>';
                });
        }
    });
</script>