<?php
// 1. ENABLE ERROR REPORTING (To catch invisible crashes)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. HEADERS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// 3. DATABASE CONNECTION
if (file_exists('db_config.php')) {
    include_once 'db_config.php';
} elseif (file_exists('db_connect.php')) {
    include_once 'db_connect.php';
} else {
    echo json_encode(["error" => "Database connection file missing."]);
    exit;
}

// 4. GET PARAMS
$role = isset($_GET['role']) ? strtolower($_GET['role']) : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

$response = [];

// 5. THE QUERY LOGIC
try {
    // --- ADMIN: Show ALL records ---
    if ($role === 'admin') {
        $sql = "SELECT * FROM complaints ORDER BY submitted_at DESC";
    }
    // --- OFFICER: Show Department records ---
    elseif ($role === 'officer') {
        $sql_dept = "SELECT * FROM users WHERE id = '$user_id'";
        
        if ($conn instanceof mysqli) {
            $res = $conn->query($sql_dept);
            $userRow = $res->fetch_assoc();
        } else {
            $stmt = $conn->prepare($sql_dept);
            $stmt->execute();
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $dept_id = $userRow['department_id'] ?? $userRow['dept_id'] ?? 0;
        $sql = "SELECT * FROM complaints WHERE (department_id = '$dept_id' OR dept_id = '$dept_id') ORDER BY submitted_at DESC";
    }
    // --- CITIZEN: Show Own records ---
    else {
        $sql = "SELECT * FROM complaints WHERE (citizen_id = '$user_id' OR user_id = '$user_id') ORDER BY submitted_at DESC";
    }

    // 6. EXECUTE QUERY
    if ($conn instanceof mysqli) {
        $result = $conn->query($sql);
        if (!$result) throw new Exception($conn->error);
        $response = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------
    // NEW: FETCH ACTUAL DEPARTMENT NAMES (Crash-proof mapping)
    // -------------------------------------------------------------
    $dept_map = [];
    try {
        $dept_sql = "SELECT * FROM departments"; 
        if ($conn instanceof mysqli) {
            $d_res = $conn->query($dept_sql);
            if ($d_res) {
                while ($d_row = $d_res->fetch_assoc()) {
                    // Smart check: grabs the name whether your column is called 'department_name', 'name', or 'dept_name'
                    $name = $d_row['department_name'] ?? $d_row['name'] ?? $d_row['dept_name'] ?? "Dept #" . $d_row['id'];
                    $dept_map[$d_row['id']] = $name;
                }
            }
        } else {
            $d_stmt = $conn->query($dept_sql);
            if ($d_stmt) {
                while ($d_row = $d_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $name = $d_row['department_name'] ?? $d_row['name'] ?? $d_row['dept_name'] ?? "Dept #" . $d_row['id'];
                    $dept_map[$d_row['id']] = $name;
                }
            }
        }
    } catch (Exception $e) {
        // If 'departments' table doesn't exist, it silently skips this so your API doesn't crash
    }

    // 7. ENRICH DATA
    foreach ($response as &$row) {
        // Check Status
        if (!isset($row['status']) && isset($row['complaint_status'])) {
            $row['status'] = $row['complaint_status'];
        }
        
        // Exact Audio Mapper
        if (!isset($row['audio_path']) && isset($row['audio_url'])) {
            $row['audio_path'] = $row['audio_url'];
        }

        // Add fake citizen names if missing
        if (!isset($row['citizen_name'])) {
            $row['citizen_name'] = "Citizen #" . ($row['citizen_id'] ?? $row['user_id'] ?? 'Unknown');
        }

        // --- NEW: SWAP DEPARTMENT ID FOR REAL TEXT NAME ---
        $d_id = $row['department_id'] ?? $row['dept_id'] ?? null;
        if ($d_id && isset($dept_map[$d_id])) {
            $row['dept_name'] = $dept_map[$d_id]; // Overwrites with real name!
        } else {
            // Fallback just in case
            if (!isset($row['dept_name'])) {
                $row['dept_name'] = "Department #" . ($d_id ?? 'Gen');
            }
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(["error" => "System Error: " . $e->getMessage()]);
}
?>