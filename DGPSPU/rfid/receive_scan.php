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
    
    // Insert into scan_logs
    $insert_stmt = $conn->prepare("INSERT INTO scan_logs (student_id, timestamp) VALUES (?, NOW())");
    $insert_stmt->bind_param("s", $student_id);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Scan recorded successfully",
            "student" => $student
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