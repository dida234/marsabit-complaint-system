<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->full_name) && !empty($data->email) && !empty($data->password) && !empty($data->department_id)) {
    
    // Check if email exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $check->bindParam(':email', $data->email);
    $check->execute();
    
    if ($check->rowCount() > 0) {
        echo json_encode(["message" => "Email already exists."]);
    } else {
        // Force Role to 'officer'
        $query = "INSERT INTO users (full_name, email, password, role, department_id) 
                  VALUES (:name, :email, :pass, 'officer', :dept)";
        
        $stmt = $conn->prepare($query);
        $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':name', $data->full_name);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':pass', $hashed_password);
        $stmt->bindParam(':dept', $data->department_id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Officer account created successfully."]);
        } else {
            echo json_encode(["message" => "Failed to create officer."]);
        }
    }
} else {
    echo json_encode(["message" => "Incomplete data."]);
}
?>