<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "rfid_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch RFID logs
$sql = "SELECT * FROM rfid_logs ORDER BY scan_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Logs</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 50%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Scanned RFID Logs</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>UID</th>
            <th>Scan Time</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['id'] . "</td>
                        <td>" . $row['uid'] . "</td>
                        <td>" . $row['scan_time'] . "</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No RFID scans yet.</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>
