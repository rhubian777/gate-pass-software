<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Connect to the database
$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Get JSON data from frontend
$data = json_decode(file_get_contents("php://input"), true);

// Validate received data
if (!isset($data["student_id"]) || !isset($data["name"]) || !isset($data["card_uid"])) {
    echo json_encode(["error" => "Missing student_id, name, or card_uid"]);
    exit();
}

$student_id = $data["student_id"];
$name = $data["name"];
$card_uid = $data["card_uid"];

// Check if card_uid is already registered
$check_sql = "SELECT * FROM students WHERE card_uid = '$card_uid'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    echo json_encode(["error" => "RFID card already registered"]);
    exit();
}

// Insert into database
$sql = "INSERT INTO students (student_id, name, card_uid) VALUES ('$student_id', '$name', '$card_uid')";
if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => "Student added successfully"]);
} else {
    echo json_encode(["error" => "Failed to add student: " . $conn->error]);
}

$conn->close();
?>
