<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Get only the single latest scan
$sql = "SELECT s.student_id, s.name, s.course, s.year, l.timestamp, l.id as scan_id 
        FROM scan_logs l 
        JOIN students s ON l.student_id = s.student_id 
        ORDER BY l.timestamp DESC 
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $scan = $result->fetch_assoc();
    
    // Format the timestamp to be more readable
    $scan['formatted_time'] = date('M d, Y h:i:s A', strtotime($scan['timestamp']));
    
    // Format the year with the appropriate suffix
    $year = $scan['year'];
    $suffix = 'th';
    
    if ($year % 10 == 1 && $year % 100 != 11) {
        $suffix = 'st';
    } elseif ($year % 10 == 2 && $year % 100 != 12) {
        $suffix = 'nd';
    } elseif ($year % 10 == 3 && $year % 100 != 13) {
        $suffix = 'rd';
    }
    
    $scan['year'] = $year . $suffix . ' Year';
    
    echo json_encode($scan);
} else {
    echo json_encode(["status" => "empty"]);
}

$conn->close();
?>