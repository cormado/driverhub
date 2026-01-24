<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

/* =========================
   🔐 Seguridad
========================= */
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['owner', 'admin'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado'
    ]);
    exit;
}

$action = $_POST['action'] ?? '';

/* =========================
   ➕ Crear / ✏️ Editar
========================= */
if ($action === 'create' || $action === 'edit') {

    $id = intval($_POST['id'] ?? 0);

    // Campos obligatorios
    $code        = $conn->real_escape_string($_POST['code'] ?? '');
    $name        = $conn->real_escape_string($_POST['name'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $points      = intval($_POST['points'] ?? 0);
    $category    = $conn->real_escape_string($_POST['category'] ?? 'General');
    $icon        = $conn->real_escape_string($_POST['icon'] ?? 'fas fa-trophy');
    $active      = isset($_POST['active']) ? 1 : 0;

    $ruleType = $conn->real_escape_string($_POST['rule_type'] ?? '');

    // Campos condicionales (permiten NULL)
    $minDistance      = ($_POST['min_distance_km'] !== '' ? intval($_POST['min_distance_km']) : 'NULL');
    $requiredJobs     = ($_POST['required_jobs'] !== '' ? intval($_POST['required_jobs']) : 'NULL');
    $requiredTotalKm  = ($_POST['required_total_km'] !== '' ? intval($_POST['required_total_km']) : 'NULL');

    /* =========================
       🧠 Normalización por tipo de regla
    ========================= */
    switch ($ruleType) {

        case 'distance_job':
            // X viajes de al menos Y km
            $requiredTotalKm = 'NULL';
            break;

        case 'distance_total':
            // X km acumulados
            $minDistance  = 'NULL';
            $requiredJobs = 'NULL';
            break;

        case 'total_jobs':
            // X viajes
            $minDistance     = 'NULL';
            $requiredTotalKm = 'NULL';
            break;

        default:
            die('Tipo de regla inválido');
    }

    /* =========================
       📝 SQL
    ========================= */
    if ($action === 'create') {

        $sql = "
            INSERT INTO achievements
            (code, name, description, points_reward, rule_type,
             min_distance_km, required_jobs, required_total_km,
             category, icon, active)
            VALUES
            ('$code', '$name', '$description', $points, '$ruleType',
             $minDistance, $requiredJobs, $requiredTotalKm,
             '$category', '$icon', $active)
        ";
    } else {

        $sql = "
            UPDATE achievements SET
                code='$code',
                name='$name',
                description='$description',
                points_reward=$points,
                rule_type='$ruleType',
                min_distance_km=$minDistance,
                required_jobs=$requiredJobs,
                required_total_km=$requiredTotalKm,
                category='$category',
                icon='$icon',
                active=$active
            WHERE id=$id
        ";
    }

    if ($conn->query($sql)) {
        header("Location: ../dashboard.php?view=manageStore&result=success&type=achievement_saved");
        exit;
    } else {
        die("Error SQL: " . $conn->error);
    }
}

/* =========================
   🗑️ Eliminar
========================= */
if ($action === 'delete') {

    $id = intval($_POST['id'] ?? 0);

    if ($id > 0) {
        $conn->query("DELETE FROM achievements WHERE id=$id");
    }

    header("Location: ../dashboard.php?view=manageStore&result=success&type=achievement_deleted");
    exit;
}
