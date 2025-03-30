<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$card_uid = isset($_GET['uid']) ? $_GET['uid'] : '';

if (!empty($card_uid)) {
    $sql = "SELECT * FROM students WHERE card_uid = '$card_uid'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_id = $row["student_id"];
        $name = $row["name"];

        $insert_log = "INSERT INTO scan_logs (student_id, name) VALUES ('$student_id', '$name')";
        $conn->query($insert_log);

        echo json_encode(["status" => "success", "student_id" => $student_id, "name" => $name]);
    } else {
        echo json_encode(["status" => "error", "message" => "Student not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No UID provided"]);
}

$conn->close();
?>
