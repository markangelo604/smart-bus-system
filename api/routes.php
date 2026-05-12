<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM routes ORDER BY id DESC");
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
} elseif ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') exit;
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO routes (origin, destination, stops, distance_km, estimated_duration_minutes, origin_lat, origin_lng, destination_lat, destination_lng, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['origin'], $data['destination'], $data['stops'], 
        $data['distance_km'], $data['estimated_duration_minutes'],
        $data['origin_lat'], $data['origin_lng'],
        $data['destination_lat'], $data['destination_lng'],
        $data['status']
    ]);
    echo json_encode(["success" => true, "message" => "Route added"]);
} elseif ($method === 'PUT') {
    if ($_SESSION['role'] !== 'admin') exit;
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE routes SET origin=?, destination=?, stops=?, distance_km=?, estimated_duration_minutes=?, origin_lat=?, origin_lng=?, destination_lat=?, destination_lng=?, status=? WHERE id=?");
    $stmt->execute([
        $data['origin'], $data['destination'], $data['stops'], 
        $data['distance_km'], $data['estimated_duration_minutes'],
        $data['origin_lat'], $data['origin_lng'],
        $data['destination_lat'], $data['destination_lng'],
        $data['status'], $data['id']
    ]);
    echo json_encode(["success" => true, "message" => "Route updated"]);
}
?>
