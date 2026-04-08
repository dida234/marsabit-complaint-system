<?php
// Handle Cross-Origin Resource Sharing (CORS) for React
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once 'db_config.php';

// Get JSON input from the frontend
$data = json_decode(file_get_contents("php://input"));

// Determine the action (register or login) from the URL parameter
$action = $_GET['action'] ?? '';

if ($action == 'register') {
    // Check for all required fields, now using phone_number
    if(!empty($data->full_name) && !empty($data->email) && !empty($data->phone_number) && !empty($data->password) && !empty($data->constituency) && !empty($data->ward)) {
        
        // check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $conn->prepare($checkQuery);
        
        // Fix: Assign to variable before binding
        $email = $data->email;
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if($checkStmt->rowCount() > 0){
             echo json_encode(["message" => "Email already exists."]);
        } else {
            // Updated phone to phone_number in the INSERT query
            $query = "INSERT INTO users (full_name, email, phone_number, password, role, constituency, ward) 
                      VALUES (:full_name, :email, :phone_number, :password, 'citizen', :constituency, :ward)";
            
            $stmt = $conn->prepare($query);

            // Securely hash the password
            $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

            // Assigning object properties to variables first
            $fullName = $data->full_name;
            $userEmail = $data->email;
            $userPhone = $data->phone_number; // UPDATED: Assign phone_number variable
            $constituency = $data->constituency;
            $ward = $data->ward;

            $stmt->bindParam(':full_name', $fullName);
            $stmt->bindParam(':email', $userEmail);
            $stmt->bindParam(':phone_number', $userPhone); // UPDATED: Bind phone_number parameter
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':constituency', $constituency);
            $stmt->bindParam(':ward', $ward);

            if($stmt->execute()) {
                echo json_encode(["message" => "User registered successfully."]);
            } else {
                echo json_encode(["message" => "Unable to register user."]);
            }
        }
    } else {
        echo json_encode(["message" => "Incomplete data."]);
    }

} elseif ($action == 'login') {
    // --- LOGIN LOGIC ---
    if(!empty($data->email) && !empty($data->password)) {
        
        // Updated phone to phone_number in the SELECT query
        $query = "SELECT id, full_name, email, phone_number, password, role, constituency, ward FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        
        $loginEmail = $data->email;
        $stmt->bindParam(':email', $loginEmail);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $password_hash = $row['password'];

            if(password_verify($data->password, $password_hash)) {
                echo json_encode([
                    "message" => "Login successful.",
                    "user" => [
                        "id" => $row['id'],
                        "full_name" => $row['full_name'],
                        "email" => $row['email'],
                        "phone_number" => $row['phone_number'], // UPDATED: Return phone_number on login
                        "role" => $row['role'],
                        "constituency" => $row['constituency'],
                        "ward" => $row['ward']
                    ]
                ]);
            } else {
                echo json_encode(["message" => "Invalid password."]);
            }
        } else {
            echo json_encode(["message" => "User not found."]);
        }
    } else {
        echo json_encode(["message" => "Incomplete data."]);
    }
} else {
    echo json_encode(["message" => "Invalid action parameter."]);
}
?>