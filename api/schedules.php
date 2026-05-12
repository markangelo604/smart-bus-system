<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'list') {
        // Admin or driver seeing all/their schedules
        $role = $_SESSION['role'];
        $query = "SELECT s.*, r.origin, r.destination, b.bus_number, u.full_name as driver_name 
                  FROM schedules s 
                  JOIN routes r ON s.route_id = r.id 
                  JOIN buses b ON s.bus_id = b.id 
                  JOIN users u ON s.driver_id = u.id";
        
        $params = [];
        if ($role === 'driver') {
            $query .= " WHERE s.driver_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        $query .= " ORDER BY s.departure_time ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
        
    } elseif ($action === 'search') {
        // Passenger searching
        $origin = $_GET['origin'] ?? '';
        $destination = $_GET['destination'] ?? '';
        $date = $_GET['date'] ?? '';
        
        $query = "SELECT s.*, r.origin, r.destination, r.distance_km, r.estimated_duration_minutes, b.type as bus_type 
                  FROM schedules s 
                  JOIN routes r ON s.route_id = r.id 
                  JOIN buses b ON s.bus_id = b.id
                  WHERE DATE(s.departure_time) = ? AND s.trip_status IN ('Scheduled', 'Boarding') AND s.available_seats > 0";
        $params = [$date];
        
        if ($origin) {
            $query .= " AND r.origin = ?";
            $params[] = $origin;
        }
        if ($destination) {
            $query .= " AND r.destination = ?";
            $params[] = $destination;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    }
} elseif ($method === 'POST') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(["success" => false, "message" => "Forbidden"]);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("INSERT INTO schedules (route_id, bus_id, driver_id, departure_time, arrival_time, fare, available_seats) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['route_id'], $data['bus_id'], $data['driver_id'], 
        $data['departure_time'], $data['arrival_time'], 
        $data['fare'], $data['available_seats']
    ]);
    
    echo json_encode(["success" => true, "message" => "Schedule created"]);
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    
    if (!$id) {
        echo json_encode(["success" => false, "message" => "ID required"]);
        exit;
    }
    
    if ($_SESSION['role'] === 'admin' && isset($data['departure_time'])) {
        $stmt = $pdo->prepare("UPDATE schedules SET route_id=?, bus_id=?, driver_id=?, departure_time=?, arrival_time=?, fare=?, available_seats=? WHERE id=?");
        $stmt->execute([
            $data['route_id'], $data['bus_id'], $data['driver_id'], 
            $data['departure_time'], $data['arrival_time'], 
            $data['fare'], $data['available_seats'], $id
        ]);
        echo json_encode(["success" => true, "message" => "Schedule updated"]);
    } elseif (isset($data['trip_status'])) {
        // Driver or Admin updating status
        if ($_SESSION['role'] === 'driver') {
            $stmt = $pdo->prepare("SELECT id FROM schedules WHERE id = ? AND driver_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                echo json_encode(["success" => false, "message" => "Forbidden"]);
                exit;
            }
        }
        $stmt = $pdo->prepare("UPDATE schedules SET trip_status = ? WHERE id = ?");
        $stmt->execute([$data['trip_status'], $id]);
        echo json_encode(["success" => true, "message" => "Status updated"]);
    }
}
?>
