<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

// 1. Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=not_logged_in");
    exit;
}

$user_id = $_SESSION['user_id'];
$reward_id = isset($_POST['reward_id']) ? intval($_POST['reward_id']) : 0;

if ($reward_id <= 0) {
    header("Location: ../index.php?vista=store&error=invalid_reward");
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 2. Obtener datos de la recompensa
    $stmt_rew = $conn->prepare("
        SELECT cost_points, stock, active, name, infinite_stock
        FROM rewards
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt_rew->bind_param("i", $reward_id);
    $stmt_rew->execute();
    $reward = $stmt_rew->get_result()->fetch_assoc();

    // Validar disponibilidad de la recompensa
    if (
        !$reward ||
        !$reward['active'] ||
        (
            !$reward['infinite_stock'] &&
            $reward['stock'] <= 0
        )
    ) {
        throw new Exception("reward_not_available");
    }

    // 3. Verificar puntos del usuario
    $stmt_pts = $conn->prepare("
        SELECT available_points
        FROM user_stats
        WHERE user_id = ?
        FOR UPDATE
    ");
    $stmt_pts->bind_param("i", $user_id);
    $stmt_pts->execute();
    $user_stats = $stmt_pts->get_result()->fetch_assoc();

    if (
        !$user_stats ||
        $user_stats['available_points'] < $reward['cost_points']
    ) {
        throw new Exception("insufficient_points");
    }

    $costo = $reward['cost_points'];

    // 4. Restar puntos al usuario
    $stmt_update_pts = $conn->prepare("
        UPDATE user_stats
        SET available_points = available_points - ?
        WHERE user_id = ?
    ");
    $stmt_update_pts->bind_param("ii", $costo, $user_id);
    $stmt_update_pts->execute();

    // 5. Restar stock SOLO si no es infinito
    if (!$reward['infinite_stock']) {
        $stmt_update_stock = $conn->prepare("
            UPDATE rewards
            SET stock = stock - 1
            WHERE id = ?
        ");
        $stmt_update_stock->bind_param("i", $reward_id);
        $stmt_update_stock->execute();
    }

    // 6. Registrar el canje
    $stmt_log = $conn->prepare("
        INSERT INTO user_rewards (user_id, reward_id, points_spent, status)
        VALUES (?, ?, ?, 'pendiente')
    ");
    $stmt_log->bind_param("iii", $user_id, $reward_id, $costo);
    $stmt_log->execute();

    // Confirmar transacción
    $conn->commit();
    header("Location: ../dashboard.php?view=store&success=redeem_ok");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../dashboard.php?view=store&error=" . $e->getMessage());
    exit;
}
