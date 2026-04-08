<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_config.php';

if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed or is not PDO."]);
    exit();
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing User ID"]);
    exit();
}

try {
    // UPDATED: 'c.*' safely grabs all columns from the complaints table, 
    // which automatically includes the new 'payment_required', 'payment_status', 
    // and 'fee_amount' fields for the M-Pesa integration.
    // 'd.dept_name' is joined so the frontend displays the department string correctly.
    $sql = "SELECT c.*, d.dept_name 
            FROM complaints c
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE c.user_id = :user_id 
            ORDER BY c.id DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($complaints);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>