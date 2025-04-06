<?php
$servername = "localhost";
$username = "root"; // adjust if needed
$password = "";     // adjust if needed
$dbname = "rfid_system"; // or whatever DB name you're using

$conn = new mysqli($servername, $username, $password, $dbname);

// Get the latest scan
$sql = "SELECT s.student_id, s.name, s.course, s.year 
        FROM rfid_logs r 
        JOIN students s ON r.student_id = s.student_id 
        ORDER BY r.timestamp DESC LIMIT 1";

$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
  echo json_encode($row);
} else {
  echo json_encode([]);
}

$conn->close();
?>
