<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db_config.php';

// Check if connection was successful in db_config.php
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['title']) && !empty($data['message'])) {
    
    // Using Prepared Statements for Security (PDO)
    try {
        $sql = "INSERT INTO announcements (title, message) VALUES (:title, :message)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':message', $data['message']);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Announcement posted"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to execute query"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Incomplete data"]);
}
?>