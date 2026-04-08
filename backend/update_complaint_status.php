<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(["success" => false, "message" => "Missing ID or Status"]);
    exit;
}

$complaintId = $data['id'];
$newStatus = $data['status'];
$officerId = $data['officer_id'] ?? null; // Capture the Officer's ID
$remarks = $data['remarks'] ?? "Status updated to $newStatus";

try {
    $conn->beginTransaction();

    // 1. Update the Main Complaint Table
    $stmt = $conn->prepare("UPDATE complaints SET status = :status WHERE id = :id");
    $stmt->bindParam(':status', $newStatus);
    $stmt->bindParam(':id', $complaintId);
    $stmt->execute();

    // 2. Insert into History Table (Make sure your table has an 'officer_id' column!)
    // If you don't have an 'officer_id' column yet, run the SQL command below this code block.
    $historySql = "INSERT INTO complaint_updates (complaint_id, status, remarks, officer_id, updated_at) 
                   VALUES (:cid, :status, :remarks, :oid, NOW())";
    
    $historyStmt = $conn->prepare($historySql);
    $historyStmt->bindParam(':cid', $complaintId);
    $historyStmt->bindParam(':status', $newStatus);
    $historyStmt->bindParam(':remarks', $remarks);
    $historyStmt->bindParam(':oid', $officerId); 

    if (!$historyStmt->execute()) {
        throw new Exception("Failed to log history.");
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Status updated successfully"]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>