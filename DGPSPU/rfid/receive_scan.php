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
        
        // After you find the student and before inserting the scan
        // Check the last scan type for this student
        $last_scan_sql = "SELECT scan_type FROM scan_logs 
                        WHERE student_id = ? 
                        ORDER BY timestamp DESC 
                        LIMIT 1";
        $last_scan_stmt = $conn->prepare($last_scan_sql);
        $last_scan_stmt->bind_param("s", $student_id);
        $last_scan_stmt->execute();
        $last_scan_result = $last_scan_stmt->get_result();
        $scan_type = "IN"; // Default to IN
        if ($last_scan_result->num_rows > 0) {
            $last_scan = $last_scan_result->fetch_assoc();
            // Toggle the scan type
            $scan_type = ($last_scan['scan_type'] == "IN" || $last_scan['scan_type'] == NULL) ? "OUT" : "IN";
        }
        $last_scan_stmt->close();
        
        // Modify your INSERT statement to include scan_type
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