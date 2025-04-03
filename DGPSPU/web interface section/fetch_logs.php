<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "rfid_system");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch scan logs with Course and Year formatted properly
$sql = "SELECT scan_logs.student_id, students.name, students.course, 
        CASE 
            WHEN students.year = 1 THEN '1st Year'
            WHEN students.year = 2 THEN '2nd Year'
            WHEN students.year = 3 THEN '3rd Year'
            WHEN students.year = 4 THEN '4th Year'
            ELSE 'Unknown Year'
        END AS year_level, 
        scan_logs.timestamp 
        FROM scan_logs 
        JOIN students ON scan_logs.student_id = students.student_id
        ORDER BY scan_logs.timestamp DESC";

$result = $conn->query($sql);

if (!$result) {
    die(json_encode(["error" => "SQL query failed: " . $conn->error]));
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Return JSON response
echo json_encode($data, JSON_PRETTY_PRINT);
$conn->close();
?>
