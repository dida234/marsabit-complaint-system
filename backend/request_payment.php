<?php
// backend/request_payment.php
header("Access-Control-Allow-Origin: *");
// UPDATE 1: Allow OPTIONS method for React CORS Preflight
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 
header("Content-Type: application/json; charset=UTF-8");

// UPDATE 2: Catch the preflight request and exit immediately so the browser knows it's safe to send the actual POST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_config.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->complaint_id) && isset($data->fee_amount)) {
    try {
        $sql = "UPDATE complaints 
                SET status = 'Awaiting Payment', 
                    payment_required = 1, 
                    fee_amount = :fee_amount, 
                    payment_status = 'Unpaid'
                WHERE id = :id";
                
        $stmt = $conn->prepare($sql);
        
        // UPDATE 3: Swapped bindParam to bindValue for safer handling of object properties
        $stmt->bindValue(':fee_amount', $data->fee_amount);
        $stmt->bindValue(':id', $data->complaint_id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Payment requested successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update database"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    // Added a 400 error code so React knows it was a bad request if data is missing
    http_response_code(400);
    echo json_encode(["error" => "Missing data. Required: complaint_id and fee_amount."]);
}
?>