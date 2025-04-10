<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// Get the student ID from the most recent scan
$latest_sql = "SELECT student_id FROM scan_logs ORDER BY timestamp DESC LIMIT 1";
$latest_result = $conn->query($latest_sql);

if ($latest_result->num_rows > 0) {
    $latest_row = $latest_result->fetch_assoc();
    $student_id = $latest_row['student_id'];
    
    // Get student info
    $student_sql = "SELECT s.student_id, s.name, s.course, s.year 
                    FROM students s 
                    WHERE s.student_id = '$student_id'";
    $student_result = $conn->query($student_sql);
    
    if ($student_result && $student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        
        // Format the year with the appropriate suffix
        $year = $student['year'];
        $suffix = 'th';
        
        if ($year % 10 == 1 && $year % 100 != 11) {
            $suffix = 'st';
        } elseif ($year % 10 == 2 && $year % 100 != 12) {
            $suffix = 'nd';
        } elseif ($year % 10 == 3 && $year % 100 != 13) {
            $suffix = 'rd';
        }
        
        $student['year'] = $year . $suffix . ' Year';
        
        // Get latest IN scan - make sure we're only looking at today's scans
        $in_sql = "SELECT timestamp, id as scan_id 
                  FROM scan_logs 
                  WHERE student_id = '$student_id' AND scan_type = 'IN'
                  AND DATE(timestamp) = CURRENT_DATE()
                  ORDER BY timestamp DESC 
                  LIMIT 1";
        $in_result = $conn->query($in_sql);
        
        // Get latest OUT scan - make sure we're only looking at today's scans
        $out_sql = "SELECT timestamp, id as scan_id 
                   FROM scan_logs 
                   WHERE student_id = '$student_id' AND scan_type = 'OUT'
                   AND DATE(timestamp) = CURRENT_DATE()
                   ORDER BY timestamp DESC 
                   LIMIT 1";
        $out_result = $conn->query($out_sql);
        
        // Prepare response
        $response = $student;
        
        if ($in_result && $in_result->num_rows > 0) {
            $in_row = $in_result->fetch_assoc();
            $response['time_in'] = date('M d, Y h:i:s A', strtotime($in_row['timestamp']));
            $response['in_scan_id'] = $in_row['scan_id'];
        } else {
            $response['time_in'] = 'Not available';
        }
        
        if ($out_result && $out_result->num_rows > 0) {
            $out_row = $out_result->fetch_assoc();
            $response['time_out'] = date('M d, Y h:i:s A', strtotime($out_row['timestamp']));
            $response['out_scan_id'] = $out_row['scan_id'];
        } else {
            $response['time_out'] = 'Not available';
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(["status" => "error", "message" => "Student not found"]);
    }
} else {
    echo json_encode(["status" => "empty", "message" => "No scan records found"]);
}

$conn->close();
?>