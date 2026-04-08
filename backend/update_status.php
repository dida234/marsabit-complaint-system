<?php
// backend/update_status.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->id) && isset($data->status)) {
    try {
        $sql = "UPDATE complaints SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$data->status, $data->id])) {
            echo json_encode(["success" => true, "message" => "Status updated to " . $data->status]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid input"]);
}
?>