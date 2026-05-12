<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($_SESSION['role'] === 'admin') {
        $stmt = $pdo->query("SELECT i.*, u.full_name as driver_name, r.origin, r.destination 
                             FROM incidents i 
                             JOIN users u ON i.driver_id = u.id 
                             JOIN schedules s ON i.schedule_id = s.id 
                             JOIN routes r ON s.route_id = r.id 
                             ORDER BY i.reported_at DESC");
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } elseif ($_SESSION['role'] === 'driver') {
        $stmt = $pdo->prepare("SELECT i.*, r.origin, r.destination 
                               FROM incidents i 
                               JOIN schedules s ON i.schedule_id = s.id 
                               JOIN routes r ON s.route_id = r.id 
                               WHERE i.driver_id = ? ORDER BY i.reported_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } else {
        echo json_encode(["success" => false, "message" => "Forbidden"]);
    }
} elseif ($method === 'POST') {
    if ($_SESSION['role'] !== 'driver') exit;
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO incidents (driver_id, schedule_id, description, incident_lat, incident_lng) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $data['schedule_id'], $data['description'], $data['incident_lat'], $data['incident_lng']]);
    echo json_encode(["success" => true, "message" => "Incident reported"]);
} elseif ($method === 'PUT') {
    if ($_SESSION['role'] !== 'admin') exit;
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE incidents SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $data['id']]);
    echo json_encode(["success" => true, "message" => "Incident updated"]);
}
?>
