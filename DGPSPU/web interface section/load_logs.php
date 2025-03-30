<?php
$host = "localhost"; // Change if needed
$user = "root";      // Change if your DB has a different user
$password = "";      // Put your DB password if there is one
$dbname = "rfid_system";

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest 10 scan logs from scan_logs table
$sql = "SELECT student_id, name, timestamp FROM scan_logs ORDER BY timestamp DESC LIMIT 10";
$result = $conn->query($sql);

// Output HTML table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['student_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No scan logs found.</td></tr>";
}

$conn->close();
?>