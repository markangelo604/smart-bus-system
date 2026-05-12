<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($action === 'login') {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode(["success" => false, "message" => "Email and password required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, full_name, role, password, status FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                echo json_encode(["success" => false, "message" => "Account is inactive"]);
                exit;
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];

            echo json_encode(["success" => true, "message" => "Login successful", "data" => [
                "id" => $user['id'],
                "role" => $user['role'],
                "name" => $user['full_name']
            ]]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid credentials"]);
        }
    } elseif ($action === 'register') {
        $full_name = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $phone = $data['phone'] ?? '';

        if (!$full_name || !$email || !$password) {
            echo json_encode(["success" => false, "message" => "Required fields missing"]);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone) VALUES (?, ?, ?, 'passenger', ?)");
            $stmt->execute([$full_name, $email, $hash, $phone]);
            echo json_encode(["success" => true, "message" => "Registration successful. Please login."]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(["success" => false, "message" => "Email already exists"]);
            } else {
                echo json_encode(["success" => false, "message" => "Registration failed"]);
            }
        }
    } elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(["success" => true, "message" => "Logged out"]);
    }
} elseif ($method === 'GET') {
    if ($action === 'session') {
        if (isset($_SESSION['user_id'])) {
            echo json_encode(["success" => true, "data" => [
                "id" => $_SESSION['user_id'],
                "role" => $_SESSION['role'],
                "name" => $_SESSION['name']
            ]]);
        } else {
            echo json_encode(["success" => false, "message" => "Not logged in"]);
        }
    }
}
?>
