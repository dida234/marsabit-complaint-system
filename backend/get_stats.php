<?php
include 'db_config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    // 1. Total Complaints
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 2. Pending
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Pending'");
    $pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 3. Resolved
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'Resolved'");
    $resolved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 4. Total Users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // 5. Critical Issues (NEW: Added this so the dashboard gets the number!)
    $stmt = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE priority_level = 'Critical'");
    $critical = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        "total_complaints" => $total,
        "pending_complaints" => $pending,
        "resolved_complaints" => $resolved,
        "total_users" => $users,
        "critical_issues" => $critical // Added to the JSON response
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>