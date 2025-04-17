<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "rfid_system");

    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed"]));
    }

    // Get the card UID from the request
    $card_uid = isset($_GET['card_uid']) ? strtoupper($_GET['card_uid']) : '';

    if (empty($card_uid)) {
        echo json_encode(["error" => "No card UID provided"]);
        exit;
    }

    // Find the student based on card_uid
    $stmt = $conn->prepare("SELECT student_id, name, course, year FROM students WHERE card_uid = ?");
    $stmt->bind_param("s", $card_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Student found
        $student = $result->fetch_assoc();
        $student_id = $student['student_id'];
        
        // Check if this student has a recent scan today
        $today = date('Y-m-d');
        $check_duplicate_sql = "SELECT id, scan_type FROM scan_logs 
                              WHERE student_id = ? 
                              AND DATE(timestamp) = ?
                              ORDER BY timestamp DESC 
                              LIMIT 1";
        $check_duplicate_stmt = $conn->prepare($check_duplicate_sql);
        $check_duplicate_stmt->bind_param("ss", $student_id, $today);
        $check_duplicate_stmt->execute();
        $duplicate_result = $check_duplicate_stmt->get_result();
        
        if ($duplicate_result->num_rows > 0) {
            $last_scan = $duplicate_result->fetch_assoc();
            $scan_type = ($last_scan['scan_type'] == "IN") ? "OUT" : "IN";
            
            // Check if the last scan was too recent (duplicate prevention)
            $recent_scan_sql = "SELECT COUNT(*) as count FROM scan_logs 
                              WHERE student_id = ? 
                              AND timestamp > DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
            $recent_scan_stmt = $conn->prepare($recent_scan_sql);
            $recent_scan_stmt->bind_param("s", $student_id);
            $recent_scan_stmt->execute();
            $recent_result = $recent_scan_stmt->get_result();
            $recent_count = $recent_result->fetch_assoc()['count'];
            $recent_scan_stmt->close();
            
            if ($recent_count > 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Duplicate scan detected. Please wait before scanning again."
                ]);
                exit;
            }
        } else {
            // No scan found today, this is the first scan of the day
            $scan_type = "IN"; // Default the first scan of the day to IN
        }
        $check_duplicate_stmt->close();
        
        // Insert the new scan
        $insert_stmt = $conn->prepare("INSERT INTO scan_logs (student_id, timestamp, scan_type) VALUES (?, NOW(), ?)");
        $insert_stmt->bind_param("ss", $student_id, $scan_type);
        
        if ($insert_stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Scan recorded successfully",
                "student" => $student,
                "scan_type" => $scan_type
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to record scan"
            ]);
        }
        
        $insert_stmt->close();
    } else {
        // Student not found - record the unknown card in rfid_logs
        $insert_stmt = $conn->prepare("INSERT INTO rfid_logs (uid, scan_time) VALUES (?, NOW())");
        $insert_stmt->bind_param("s", $card_uid);
        $insert_stmt->execute();
        $insert_stmt->close();
        
        echo json_encode([
            "status" => "error",
            "message" => "Unknown card UID"
        ]);
    }

    $stmt->close();
    $conn->close();
?>