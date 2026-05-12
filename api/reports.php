<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "Forbidden"]);
    exit;
}

$action = $_GET['action'] ?? 'kpi';

if ($action === 'kpi') {
    $today = date('Y-m-d');
    
    // Total Bookings Today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(booked_at) = ? AND status = 'confirmed'");
    $stmt->execute([$today]);
    $bookings_today = $stmt->fetchColumn();
    
    // Active Buses
    $stmt = $pdo->query("SELECT COUNT(*) FROM buses WHERE status = 'active'");
    $active_buses = $stmt->fetchColumn();
    
    // Revenue Today
    $stmt = $pdo->prepare("SELECT SUM(fare_paid) FROM bookings WHERE DATE(booked_at) = ? AND status = 'confirmed'");
    $stmt->execute([$today]);
    $revenue_today = $stmt->fetchColumn() ?? 0;
    
    // Total Passengers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'passenger'");
    $total_passengers = $stmt->fetchColumn();
    
    echo json_encode(["success" => true, "data" => [
        "bookings_today" => $bookings_today,
        "active_buses" => $active_buses,
        "revenue_today" => $revenue_today,
        "total_passengers" => $total_passengers
    ]]);
} elseif ($action === 'summary') {
    $stmt = $pdo->query("SELECT DATE(booked_at) as date, COUNT(*) as total_bookings, SUM(fare_paid) as total_revenue 
                         FROM bookings WHERE status = 'confirmed' 
                         GROUP BY DATE(booked_at) ORDER BY date DESC LIMIT 30");
    echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
}
?>
