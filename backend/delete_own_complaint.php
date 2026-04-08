<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(["success" => false, "message" => "No ID provided"]);
    exit;
}

$id = $data['id'];

try {
    // PDO Prepared Statement
    $stmt = $conn->prepare("DELETE FROM complaints WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Check if any row was actually deleted
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Complaint deleted"]);
    } else {
        echo json_encode(["success" => false, "message" => "Complaint not found or could not be deleted"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

// In PDO, connection closes automatically when script ends
?>