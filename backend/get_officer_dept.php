<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include 'db_config.php';

$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id) {
    try {
        // We select 'department_id' (from users) and 'dept_name' (from departments)
        // We alias department_id as 'dept_id' so the frontend uses a consistent name
        $sql = "SELECT u.id, u.department_id AS dept_id, d.dept_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Returns: { "id": 5, "dept_id": 1, "dept_name": "Health Services" }
            echo json_encode($row);
        } else {
            echo json_encode(["error" => "User not found"]);
        }

    } catch (PDOException $e) {
        echo json_encode(["error" => "Database Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "No ID provided"]);
}
?>