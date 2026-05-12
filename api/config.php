<?php
// Set CORS and content type
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Simple .env loader for local development
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Environment variables fallback to local WAMP defaults
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'smart_bus';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

$google_maps_key = getenv('GOOGLE_MAPS_API_KEY') ?: 'YOUR_API_KEY_HERE';

if (isset($_GET['action']) && $_GET['action'] == 'maps_key') {
    echo json_encode(["success" => true, "data" => $google_maps_key]);
    exit();
}

// Check if we should use a Unix socket (for Google Cloud Run) or a standard host
if (strpos($db_host, '/') === 0) {
    // DB_HOST is a path (starts with /), so use unix_socket
    $dsn = "mysql:unix_socket=$db_host;dbname=$db_name;charset=utf8mb4";
} else {
    // DB_HOST is a hostname or IP, use standard host
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $e->getMessage(),
        "debug_info" => [
            "host" => $db_host,
            "database" => $db_name,
            "user" => $db_user,
            "env_loaded" => file_exists($env_path)
        ]
    ]);
    exit();
}

// Ensure session is started for all API endpoints
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
