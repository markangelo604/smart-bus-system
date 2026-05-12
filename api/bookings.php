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
        $role = $_SESSION['role'];
        $query = "SELECT b.*, s.departure_time, s.arrival_time, r.origin, r.destination, s.trip_status, u.full_name as passenger_name
                  FROM bookings b 
                  JOIN schedules s ON b.schedule_id = s.id 
                  JOIN routes r ON s.route_id = r.id
                  JOIN users u ON b.passenger_id = u.id";
        
        $params = [];
        if ($role === 'passenger') {
            $query .= " WHERE b.passenger_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        $query .= " ORDER BY b.booked_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
    } elseif ($action === 'seats') {
        $schedule_id = $_GET['schedule_id'] ?? 0;
        $stmt = $pdo->prepare("SELECT seat_number FROM bookings WHERE schedule_id = ? AND status = 'confirmed'");
        $stmt->execute([$schedule_id]);
        echo json_encode(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    }
} elseif ($method === 'POST') {
    if ($_SESSION['role'] !== 'passenger') {
        echo json_encode(["success" => false, "message" => "Only passengers can book"]);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $schedule_id = $data['schedule_id'];
    $seat_number = $data['seat_number'];
    
    $pdo->beginTransaction();
    try {
        // Check schedule availability
        $stmt = $pdo->prepare("SELECT fare, available_seats FROM schedules WHERE id = ? FOR UPDATE");
        $stmt->execute([$schedule_id]);
        $schedule = $stmt->fetch();
        
        if (!$schedule || $schedule['available_seats'] <= 0) {
            throw new Exception("Schedule not available");
        }
        
        // Check if seat taken
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE schedule_id = ? AND seat_number = ? AND status = 'confirmed'");
        $stmt->execute([$schedule_id, $seat_number]);
        if ($stmt->fetch()) {
            throw new Exception("Seat already taken");
        }
        
        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (passenger_id, schedule_id, seat_number, fare_paid) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $schedule_id, $seat_number, $schedule['fare']]);
        
        // Update seats
        $stmt = $pdo->prepare("UPDATE schedules SET available_seats = available_seats - 1 WHERE id = ?");
        $stmt->execute([$schedule_id]);
        
        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Booking confirmed"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $status = $data['status']; // 'cancelled'
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT schedule_id FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            if ($status === 'cancelled') {
                $stmt = $pdo->prepare("UPDATE schedules SET available_seats = available_seats + 1 WHERE id = ?");
                $stmt->execute([$booking['schedule_id']]);
            }
        }
        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Booking updated"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Failed to update booking"]);
    }
}
?>
