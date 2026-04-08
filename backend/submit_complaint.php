<?php
// 1. SETUP HEADERS & ERROR LOGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// 2. INCLUDE YOUR CONFIG (PDO Connection)
try {
    include 'db_config.php';
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Connection File Error"]);
    exit();
}

// 3. CHECK IF DATA WAS SENT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CAPTURE DATA (Using null coalescing operator ?? for safety) ---
    // Capture user_id (default to 0 if not sent)
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    $citizen_name = $_POST['citizen_name'] ?? 'Anonymous';
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['description'] ?? '';
    $sub_county = $_POST['sub_county'] ?? 'Unknown';
    $ward = $_POST['ward'] ?? 'Unknown';

    // --- CAPTURE AI DEPARTMENT ID ---
    // If AI assigned an ID, use it. If not, default to 15 (Administration).
    $dept_id = 15;
    if (isset($_POST['department_id']) && is_numeric($_POST['department_id'])) {
        $dept_id = intval($_POST['department_id']);
    }

    // --- CAPTURE PRIORITY LEVEL ---
    // We capture the priority sent from the frontend (which got it from Python).
    // If for some reason it's missing, we default to 'Normal'.
    $priority = $_POST['priority_level'] ?? 'Normal';

    // Ensure uploads directory exists for both images and audio
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

    // --- HANDLE IMAGE UPLOAD ---
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // --- NEW: FIXED AUDIO UPLOAD HANDLING ---
    // Instead of looking in $_POST, we look in $_FILES for the 'audio' blob sent by React
    $audio_url = null;
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] == 0) {
        $audio_file_name = time() . "_" . basename($_FILES["audio"]["name"]); // Usually voice_note.webm
        $audio_target_file = $target_dir . $audio_file_name;
        
        // Move the file from PHP's temporary storage to your uploads folder
        if (move_uploaded_file($_FILES["audio"]["tmp_name"], $audio_target_file)) {
            $audio_url = $audio_target_file; // Save the path so it goes into the database
        }
    }

    // --- INSERT INTO DATABASE (PDO STYLE) ---
    try {
        // Updated SQL query to include user_id AND audio_url
        // Added 'audio_url' column and an extra '?' placeholder at the end before status
        $sql = "INSERT INTO complaints 
                (user_id, citizen_name, phone, department_id, complaint_text, sub_county, ward_location, image_path, priority_level, audio_url, status, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

        $stmt = $conn->prepare($sql);

        // Execute returns true if successful
        // IMPORTANT: Added $audio_url to the array below to match the new '?' placeholder
        if ($stmt->execute([$user_id, $citizen_name, $phone, $dept_id, $description, $sub_county, $ward, $image_path, $priority, $audio_url])) {
            echo json_encode([
                "status" => "success",
                "message" => "Report submitted successfully!",
                "assigned_dept" => $dept_id,
                "priority_assigned" => $priority,
                "audio_saved" => $audio_url // Helpful to see the path in React's Network tab if needed!
            ]);
        } else {
            // Log specific error if execute fails
            $errorInfo = $stmt->errorInfo();
            echo json_encode(["status" => "error", "message" => "Database Rejected: " . $errorInfo[2]]);
        }

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>