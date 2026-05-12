<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Forbidden"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM buses ORDER BY id DESC");
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO buses (bus_number, plate_number, capacity, type, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['bus_number'], $data['plate_number'], $data['capacity'], $data['type'], $data['status']]);
    echo json_encode(["success" => true, "message" => "Bus added"]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE buses SET bus_number=?, plate_number=?, capacity=?, type=?, status=? WHERE id=?");
    $stmt->execute([$data['bus_number'], $data['plate_number'], $data['capacity'], $data['type'], $data['status'], $data['id']]);
    echo json_encode(["success" => true, "message" => "Bus updated"]);
}
?>
