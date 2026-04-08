<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

// Connect to Database
$host = "localhost";
$db_name = "county_project_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(["status" => "error", "message" => "Connection error: " . $exception->getMessage()]);
    exit();
}

// Get the Officer's Department ID from the URL
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

if ($department_id > 0) {
    try {
        // 1. Get Total Complaints
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM complaints WHERE department_id = :dept_id");
        $stmt->execute(['dept_id' => $department_id]);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. Get Counts by Status
        $stmt = $conn->prepare("
            SELECT status, COUNT(*) as count 
            FROM complaints 
            WHERE department_id = :dept_id 
            GROUP BY status
        ");
        $stmt->execute(['dept_id' => $department_id]);
        $status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get Counts by Priority
        $stmt = $conn->prepare("
            SELECT priority_level, COUNT(*) as count 
            FROM complaints 
            WHERE department_id = :dept_id 
            GROUP BY priority_level
        ");
        $stmt->execute(['dept_id' => $department_id]);
        $priority_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the data nicely for React
        $analytics = [
            "total" => $total,
            "status" => [
                "pending" => 0,
                "in_progress" => 0,
                "resolved" => 0
            ],
            "priority" => [
                "Normal" => 0,
                "High" => 0,
                "Critical" => 0
            ]
        ];

        // Map status results
        foreach ($status_counts as $row) {
            $status = strtolower(str_replace(' ', '_', $row['status'])); // normalize keys
            $analytics['status'][$status] = $row['count'];
        }

        // Map priority results
        foreach ($priority_counts as $row) {
            $analytics['priority'][$row['priority_level']] = $row['count'];
        }

        echo json_encode(["status" => "success", "data" => $analytics]);

    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Query failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Department ID."]);
}
?>