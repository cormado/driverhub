<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Solo permitir a Owner o Admin
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
    $cost = intval($_POST['cost']);
    $stock = intval($_POST['stock']);
    $active = isset($_POST['active']) ? 1 : 0;

    if ($action === 'create') {
        $sql = "INSERT INTO rewards (code, name, description, cost_points, stock, active) 
                VALUES ('$code', '$name', '$description', $cost, $stock, $active)";
    } else {
        $sql = "UPDATE rewards SET code='$code', name='$name', description='$description', 
                cost_points=$cost, stock=$stock, active=$active WHERE id=$id";
    }

    if ($conn->query($sql)) {
        header("Location: ../dashboard.php?view=manageStore&success=1");
    } else {
        echo "Error: " . $conn->error;
    }
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM rewards WHERE id=$id");
    header("Location: ../dashboard.php?view=manageStore&deleted=1");
}