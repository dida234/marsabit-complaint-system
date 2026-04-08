<?php
header("Access-Control-Allow-Origin: *");
include_once 'db_config.php';

$query = "SELECT u.id, u.full_name, u.email, d.dept_name 
          FROM users u 
          LEFT JOIN departments d ON u.department_id = d.id 
          WHERE u.role = 'officer'";
$stmt = $conn->prepare($query);
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>