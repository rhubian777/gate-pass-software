<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "rfid_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];

    // Get student details by card_uid
    $stmt = $conn->prepare("SELECT student_id, name FROM students WHERE card_uid = ?");
    $stmt->bind_param("s", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student) {
        $student_id = $student['student_id'];
        $name = $student['name'];

        // Check last scan time
        $stmt = $conn->prepare("SELECT timestamp FROM scan_logs WHERE student_id = ? ORDER BY timestamp DESC LIMIT 1");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lastScan = $result->fetch_assoc();

        $allowScan = true;
        if ($lastScan) {
            $lastTime = strtotime($lastScan['timestamp']);
            $currentTime = time();
            if (($currentTime - $lastTime) < 5) {
                $allowScan = false;
            }
        }

        if ($allowScan) {
            // Insert into scan_logs
            $stmt = $conn->prepare("INSERT INTO scan_logs (student_id, name) VALUES (?, ?)");
            $stmt->bind_param("ss", $student_id, $name);
            $stmt->execute();
            echo "Scan logged successfully.";
        } else {
            echo "Duplicate scan detected. Please wait a moment.";
        }
    } else {
        echo "UID not registered.";
    }

    $stmt->close();
} else {
    echo "UID not provided.";
}

$conn->close();
?>