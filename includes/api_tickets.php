<?php
// includes/api_tickets.php
session_start();
require 'db.php'; // Asegúrate que este archivo existe en la misma carpeta

// Verificar seguridad básica
if (!isset($_SESSION['user_id'])) { die("Acceso denegado"); }

$my_id = $_SESSION['user_id'];
$my_role = $_SESSION['role']; // Asegúrate de tener esto en tu login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $accion = $_POST['action'] ?? '';
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;

    // --- 1. ENVIAR MENSAJE (CHAT) ---
    if ($accion === 'reply' && $ticket_id > 0) {
        $msg = $conn->real_escape_string($_POST['message']);
        
        // Insertar mensaje
        $conn->query("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES ($ticket_id, $my_id, '$msg')");
        
        // Si responde el staff, cambiar estado a 'en_proceso' (si estaba abierto)
        if ($my_role != 'conductor') {
            $conn->query("UPDATE tickets SET status = 'en_proceso' WHERE id = $ticket_id AND status = 'abierto'");
        }
    }

    // --- 2. CREAR NUEVO TICKET ---
    if ($accion === 'create') {
        $subject = $conn->real_escape_string($_POST['subject']);
        $msg = $conn->real_escape_string($_POST['message']);
        
        if($conn->query("INSERT INTO tickets (author_id, subject) VALUES ($my_id, '$subject')")) {
            $new_id = $conn->insert_id;
            $conn->query("INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES ($new_id, $my_id, '$msg')");
            echo $new_id; // Devolvemos el ID para redireccionar con JS
            exit;
        }
    }

    // --- 3. RECLAMAR TICKET ---
    if ($accion === 'claim' && $ticket_id > 0) {
        $conn->query("UPDATE tickets SET assigned_to = $my_id, status = 'en_proceso' WHERE id = $ticket_id");
    }

    // --- 4. CERRAR TICKET ---
    if ($accion === 'close' && $ticket_id > 0) {
        // Guardamos la fecha de cierre
        $conn->query("UPDATE tickets SET status = 'resuelto', closed_at = NOW() WHERE id = $ticket_id");
    }

    // --- 5. ARCHIVAR TICKET ---
    if ($accion === 'archive' && $ticket_id > 0) {
        $conn->query("UPDATE tickets SET status = 'archived' WHERE id = $ticket_id");
    }

    // --- 6. BORRAR TICKET ---
    if ($accion === 'delete' && $ticket_id > 0) {
        // Borrar mensajes primero
        $conn->query("DELETE FROM ticket_messages WHERE ticket_id = $ticket_id");
        // Borrar ticket
        $conn->query("DELETE FROM tickets WHERE id = $ticket_id");
    }
}
?>