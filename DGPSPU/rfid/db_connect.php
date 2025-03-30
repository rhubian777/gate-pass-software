<?php
$servername = "localhost";  // Change this if using a remote database
$username = "root";         // Default XAMPP username is 'root'
$password = "";             // Default XAMPP password is empty
$dbname = "rfid_system";         // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<?