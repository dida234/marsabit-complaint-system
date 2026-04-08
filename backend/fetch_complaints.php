<?php
// backend/fetch_complaints.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_config.php'; 

// Auto-detect connection variable ($conn or $pdo)
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

if (!isset($conn)) {
    echo json_encode(["error" => "Database connection failed."]);
    exit;
}

$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : '';

if (!$dept_id) {
    echo json_encode([]);
    exit;
}

try {
    // --- THE FIX IS HERE ---
    // We changed 'ORDER BY created_at' to 'ORDER BY submitted_at'
    // If 'submitted_at' also fails, change it to 'ORDER BY id DESC'
    $sql = "SELECT * FROM complaints WHERE department_id = :id ORDER BY submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $dept_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch as Associative Array
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($complaints);

} catch (PDOException $e) {
    // If this fails, look at the error log again
    echo json_encode(["error" => "SQL Error: " . $e->getMessage()]);
}
?>