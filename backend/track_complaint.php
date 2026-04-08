<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'db_config.php';

// Get the ticket ID from the URL (e.g., ?id=15)
$ticket_id = $_GET['id'] ?? '';

if (empty($ticket_id)) {
    echo json_encode(["status" => "error", "message" => "Please provide a valid Ticket ID."]);
    exit;
}

try {
    // FIX: Join the tables and grab 'd.dept_name', passing it to React as 'department_name'
    $sql = "SELECT c.id, c.submitted_at, c.complaint_text, c.status, c.priority_level, 
                   d.dept_name AS department_name 
            FROM complaints c
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE c.id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ticket_id]);
    
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($complaint) {
        echo json_encode(["status" => "success", "data" => $complaint]);
    } else {
        echo json_encode(["status" => "error", "message" => "No record found with that Ticket ID."]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
}
?>