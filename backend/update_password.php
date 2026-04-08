<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$userId = $data['user_id'] ?? null;
$currentPass = $data['current_password'] ?? '';
$newPass = $data['new_password'] ?? '';

if (!$userId || !$currentPass || !$newPass) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

try {
    // 1. Verify Current Password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPass, $user['password'])) {
        echo json_encode(["success" => false, "message" => "Incorrect current password"]);
        exit;
    }

    // 2. Update to New Password
    $hashedNewPass = password_hash($newPass, PASSWORD_DEFAULT);
    
    $updateStmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
    $updateStmt->bindParam(':password', $hashedNewPass);
    $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update password"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>