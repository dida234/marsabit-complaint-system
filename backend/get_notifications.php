<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_config.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

try {
    // Fetch notifications meant for this specific user OR meant for everyone (user_id IS NULL)
    $sql = "SELECT * FROM notifications 
            WHERE user_id = :user_id OR user_id IS NULL 
            ORDER BY created_at DESC 
            LIMIT 10";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($notifications);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>