<?php
// backend/test_debug.php
header("Content-Type: text/plain");
include 'db_config.php';

// 1. CHANGE THIS to the ID of the Officer you are testing with
$officer_user_id = 5; 

echo "--- DEBUGGING STARTED ---\n";
echo "Testing with Officer User ID: " . $officer_user_id . "\n\n";

try {
    // A. Check Officer's Department
    $stmt = $conn->prepare("SELECT id, full_name, department_id FROM users WHERE id = ?");
    $stmt->execute([$officer_user_id]);
    $officer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$officer) {
        die("ERROR: Officer not found in 'users' table.\n");
    }

    $dept_id = $officer['department_id'];
    echo "Officer Name: " . $officer['full_name'] . "\n";
    echo "Officer Assigned Dept ID: " . ($dept_id ? $dept_id : "NULL (No Department Assigned!)") . "\n\n";

    if (!$dept_id) {
        die("CRITICAL ERROR: This officer has no department_id in the 'users' table. They cannot see any complaints.\n");
    }

    // B. Check Complaints for this Department
    echo "--- CHECKING COMPLAINTS TABLE ---\n";
    echo "Searching for complaints where department_id = " . $dept_id . "...\n";

    $stmt = $conn->prepare("SELECT id, complaint_text, department_id FROM complaints WHERE department_id = ?");
    $stmt->execute([$dept_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($complaints) . " complaints.\n";

    if (count($complaints) > 0) {
        print_r($complaints);
    } else {
        echo "\nWHY IS THIS 0?\n";
        echo "1. Go to your 'complaints' table in phpMyAdmin.\n";
        echo "2. Look at the 'department_id' column.\n";
        echo "3. It is probably '0' or 'NULL'. It MUST be '$dept_id' for this officer to see it.\n";
    }

} catch (Exception $e) {
    echo "SQL ERROR: " . $e->getMessage();
}
?>