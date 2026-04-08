<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->full_name) && !empty($data->email)) {
    
    // Only update password if provided
    $sql = "UPDATE users SET full_name = :name, email = :email";
    if (!empty($data->password)) {
        $sql .= ", password = :pass";
    }
    $sql .= " WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $data->full_name);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindParam(':id', $data->id);
    
    if (!empty($data->password)) {
        $hashed = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':pass', $hashed);
    }

    if ($stmt->execute()) {
        echo json_encode(["message" => "Profile updated successfully.", "status" => "success"]);
    } else {
        echo json_encode(["message" => "Update failed."]);
    }
}
?>