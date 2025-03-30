<?php
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password
$dbname = "rfid_system";  

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get RFID UID from request
$card_uid = isset($_GET['uid']) ? $_GET['uid'] : '';

if (!empty($card_uid)) {
    $sql = "SELECT * FROM students WHERE card_uid = '$card_uid'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            "status" => "success",
            "student_id" => $row["student_id"],
            "name" => $row["name"]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Student not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No UID provided"]);
}

$conn->close();
?>