<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

try {
    // 1. Total Complaints
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 2. Pending Complaints
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'");
    $pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 3. Resolved Complaints
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Resolved'");
    $resolved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 4. Total Users (Citizens + Officers)
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        "total_complaints" => $total,
        "pending_complaints" => $pending,
        "resolved_complaints" => $resolved,
        "total_users" => $users
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>