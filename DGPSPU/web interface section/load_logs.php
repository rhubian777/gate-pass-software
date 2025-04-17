<?php
$host = "localhost"; 
$user = "root";      
$password = "";      
$dbname = "rfid_system";

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch scan logs with student information
$sql = "SELECT l.student_id, s.name, l.timestamp, s.year, s.course, l.scan_type 
        FROM scan_logs l
        JOIN students s ON l.student_id = s.student_id
        ORDER BY l.timestamp DESC 
        LIMIT 20";
$result = $conn->query($sql);

// Output HTML table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['year']) . "</td>";
        echo "<td>" . htmlspecialchars($row['course']) . "</td>";
        echo "<td><span class='status-" . strtolower($row['scan_type']) . "'>" . htmlspecialchars($row['scan_type']) . "</span></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No scan logs found.</td></tr>";
}

$conn->close();
?>