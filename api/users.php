<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Forbidden"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    $role = $_GET['role'] ?? '';
    $query = "SELECT id, full_name, email, role, phone, status, created_at FROM users";
    $params = [];
    if ($role) {
        $query .= " WHERE role = ?";
        $params[] = $role;
    }
    $query .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $hash = password_hash($data['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$data['full_name'], $data['email'], $hash, $data['role'], $data['phone'], $data['status']]);
    echo json_encode(["success" => true, "message" => "User added"]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data['password'])) {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, password=?, role=?, phone=?, status=? WHERE id=?");
        $stmt->execute([$data['full_name'], $data['email'], $hash, $data['role'], $data['phone'], $data['status'], $data['id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, phone=?, status=? WHERE id=?");
        $stmt->execute([$data['full_name'], $data['email'], $data['role'], $data['phone'], $data['status'], $data['id']]);
    }
    echo json_encode(["success" => true, "message" => "User updated"]);
}
?>
