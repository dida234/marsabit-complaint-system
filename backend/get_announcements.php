<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db_config.php';

// Check if connection was successful
if (!$conn) {
    echo json_encode([]);
    exit;
}

try {
    // Fetch the 3 most recent announcements
    $sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no announcements, return empty array
    echo json_encode($announcements ?: []);

} catch (PDOException $e) {
    // If table doesn't exist or other error, return empty array to prevent crash
    echo json_encode([]);
}
?>