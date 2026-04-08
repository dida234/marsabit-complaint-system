<?php
include 'db_config.php';

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ==========================================
// 1. FETCH USER PROFILE (GET REQUEST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['id'] ?? null;

    if (!$userId) {
        echo json_encode(["error" => "User ID required"]);
        exit;
    }

    try {
        // Removed national_id from the SELECT statement
        $stmt = $conn->prepare("SELECT id, full_name, email, role, created_at, phone_number FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// 2. UPDATE USER PROFILE (POST REQUEST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $userId = $data['id'] ?? null;

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "User ID required"]);
        exit;
    }

    try {
        $fullName = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $phoneNumber = $data['phone_number'] ?? ''; 
        
        // Password update logic
        $passQuery = "";
        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $passQuery = ", password = :password";
        }

        $sql = "UPDATE users SET full_name = :name, email = :email, phone_number = :phone $passQuery WHERE id = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':name', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phoneNumber);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        if (!empty($data['password'])) {
            $stmt->bindParam(':password', $password);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
    }
}
?>