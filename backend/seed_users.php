<?php
include_once 'db_config.php';

// The password for all test accounts will be "123456"
$password = "123456";
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

try {
    // 1. Create a Citizen Account
    $sql1 = "INSERT INTO users (full_name, email, password, role) 
             VALUES ('John Citizen', 'citizen@county.com', '$hashed_password', 'citizen')";
    $conn->exec($sql1);
    echo "Test Citizen created (Email: citizen@county.com / Pass: 123456)<br>";

    // 2. Create an Admin Account
    $sql2 = "INSERT INTO users (full_name, email, password, role) 
             VALUES ('System Admin', 'admin@county.com', '$hashed_password', 'admin')";
    $conn->exec($sql2);
    echo "Test Admin created (Email: admin@county.com / Pass: 123456)<br>";

    // 3. Create an Officer Account (Assigned to Dept ID 1 - Water)
    $sql3 = "INSERT INTO users (full_name, email, password, role, department_id) 
             VALUES ('Water Officer', 'water@county.com', '$hashed_password', 'officer', 1)";
    $conn->exec($sql3);
    echo "Test Officer created (Email: water@county.com / Pass: 123456)<br>";

} catch(PDOException $e) {
    // If users already exist, this prevents a crash
    echo "Error (Users might already exist): " . $e->getMessage();
}
?>