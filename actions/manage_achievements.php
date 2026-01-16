<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

// Validación de sesión y rol
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['owner', 'admin'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create' || $action === 'edit') {
    $id = $_POST['id'] ?? null;
    $code = $conn->real_escape_string($_POST['code']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $points = intval($_POST['points']);
    $category = $conn->real_escape_string($_POST['category']);
    $icon = $conn->real_escape_string($_POST['icon']);
    $active = isset($_POST['active']) ? 1 : 0;

    if ($action === 'create') {
        $sql = "INSERT INTO achievements (code, name, description, points_reward, category, icon, active) 
                VALUES ('$code', '$name', '$description', $points, '$category', '$icon', $active)";
    } else {
        $sql = "UPDATE achievements SET 
                code='$code', name='$name', description='$description', 
                points_reward=$points, category='$category', icon='$icon', active=$active 
                WHERE id=$id";
    }

    if ($conn->query($sql)) {
        header("Location: ../dashboard.php?view=manageStore&success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM achievements WHERE id=$id");
    header("Location: ../dashboard.php?view=manageStore&deleted=1");
}