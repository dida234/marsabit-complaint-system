<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    try {
        // Delete related updates first (foreign key constraint)
        $conn->prepare("DELETE FROM complaint_updates WHERE complaint_id = :id")->execute([':id' => $data->id]);
        
        // Delete the complaint
        $stmt = $conn->prepare("DELETE FROM complaints WHERE id = :id");
        if ($stmt->execute([':id' => $data->id])) {
            echo json_encode(["message" => "Complaint deleted."]);
        } else {
            echo json_encode(["message" => "Failed to delete."]);
        }
    } catch (Exception $e) {
        echo json_encode(["message" => "Error: " . $e->getMessage()]);
    }
}
?>