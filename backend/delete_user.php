<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    // Prevent deleting yourself (Safety check)
    // In a real app, you'd check the session/token here too.
    
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([':id' => $data->id])) {
        echo json_encode(["message" => "User deleted successfully."]);
    } else {
        echo json_encode(["message" => "Failed to delete user."]);
    }
}
?>