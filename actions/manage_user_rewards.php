<?php
require_once __DIR__ . "/../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if (!$id || !in_array($action, ['entregado', 'cancelado'])) {
        exit('Invalid request');
    }

    // Iniciamos transacción
    $conn->begin_transaction();

    try {
        // Obtenemos datos del canje
        $stmt = $conn->prepare("SELECT user_id, points_spent, status FROM user_rewards WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $reward = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$reward) {
            throw new Exception("User reward not found");
        }

        // Solo podemos actualizar si está pendiente
        if ($reward['status'] !== 'pendiente') {
            throw new Exception("Only pending rewards can be updated");
        }

        // Actualizamos el estado
        $stmt = $conn->prepare("UPDATE user_rewards SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
        $stmt->close();

        // Si se canceló, devolvemos los puntos al usuario
        if ($action === 'cancelado') {
            $stmt = $conn->prepare("
                UPDATE user_stats 
                SET available_points = available_points + ? 
                WHERE user_id = ?
            ");
            $stmt->bind_param("ii", $reward['points_spent'], $reward['user_id']);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        header("Location: ../dashboard.php?view=manageStore&result=success&type=user_reward_$action");
        exit;
    } catch (Throwable $e) {
        $conn->rollback();
        header("Location: ../dashboard.php?view=manageStore&result=error&type=transaction_failed");
        exit;
    }
}
