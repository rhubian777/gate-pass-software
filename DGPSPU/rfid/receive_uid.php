<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "rfid_system");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Set timezone to match your local time
date_default_timezone_set("Asia/Manila");

// Check if UID is provided
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
    
    // Get student details from the database
    $stmt = $conn->prepare("SELECT student_id, name, course, year FROM students WHERE card_uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $name = $row['name'];
        $course = $row['course'];
        $year = $row['year'];

        // Prevent duplicate scans within the last 5 seconds
        $stmt = $conn->prepare("SELECT * FROM scan_logs WHERE student_id = ? AND timestamp >= NOW() - INTERVAL 5 SECOND");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $check_duplicate = $stmt->get_result();

        if ($check_duplicate->num_rows == 0) {
            // Insert new scan log
            $stmt = $conn->prepare("INSERT INTO scan_logs (student_id, timestamp) VALUES (?, NOW())");
            $stmt->bind_param("s", $student_id);
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "student_id" => $student_id,
                    "name" => $name,
                    "course" => $course,
                    "year" => $year,
                    "timestamp" => date("Y-m-d H:i:s"),
                    "message" => "Scan logged successfully"
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to log scan"]);
            }
        } else {
            echo json_encode(["status" => "duplicate", "message" => "Duplicate scan detected, ignored"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "UID not registered: " . $uid]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No UID provided"]);
}

$conn->close();
?>