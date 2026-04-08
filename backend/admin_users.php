<?php
include 'db_config.php'; // Returns a PDO connection object ($conn)

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// --- GET REQUEST: FETCH USERS ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Prepare Query
        $sql = "SELECT id, full_name, email, role, created_at FROM users ORDER BY id DESC";
        $stmt = $conn->query($sql);

        // Fetch All Data (PDO style)
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON
        echo json_encode($users);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
    exit;
}

// --- POST REQUEST: DELETE USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['id'])) {
        echo json_encode(["success" => false, "message" => "No ID provided"]);
        exit;
    }

    $id = $data['id'];

    // Prevent deleting Super Admin (ID 1)
    if ($id == 1) {
        echo json_encode(["success" => false, "message" => "Cannot delete Super Admin"]);
        exit;
    }

    try {
        // PDO Prepared Statement
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Delete failed"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}
?>