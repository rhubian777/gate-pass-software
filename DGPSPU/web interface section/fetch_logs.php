<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Get scan logs with student information
$sql = "SELECT l.id as scan_id, 
               l.student_id, 
               l.timestamp, 
               s.name, 
               s.course, 
               s.year,
               IFNULL(s.course, 'N/A') as course  /* Ensures course is never NULL */
        FROM scan_logs l 
        LEFT JOIN students s ON l.student_id = s.student_id 
        ORDER BY l.timestamp DESC 
        LIMIT 50"; 

$result = $conn->query($sql);
$logs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format the year with the appropriate suffix
        if (!empty($row['year'])) {
            $year = $row['year'];
            $suffix = 'th';
            
            if ($year % 10 == 1 && $year % 100 != 11) {
                $suffix = 'st';
            } elseif ($year % 10 == 2 && $year % 100 != 12) {
                $suffix = 'nd';
            } elseif ($year % 10 == 3 && $year % 100 != 13) {
                $suffix = 'rd';
            }
            
            $row['year'] = $year . $suffix . ' Year';
        }
        
        // Make sure course is never empty
        if (empty($row['course'])) {
            $row['course'] = 'N/A';
        }
        
        // Format timestamp
        if (!empty($row['timestamp'])) {
            $row['timestamp'] = date('M d, Y h:i:s A', strtotime($row['timestamp']));
        }
        
        $logs[] = $row;
    }
}

echo json_encode($logs);
$conn->close();
?>