<?php
include("../rfid/db_connect.php"); // Database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['name']);
    $card_uid = trim($_POST['rfid_uid']);

    // Check if fields are empty
    if (empty($student_id) || empty($student_name) || empty($card_uid)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: '⚠️ Missing Information',
                    text: 'All fields are required!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'add_student.php';
                });
            });
        </script>";
        exit();
    }

    // Insert into database
    $query = "INSERT INTO students (student_id, name, card_uid) VALUES ('$student_id', '$student_name', '$card_uid')";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Include SweetAlert2 -->
</head>
<body>

    <div class="add-student-container">
        <h2>Add Student</h2>
        <form action="" method="POST"> <!-- Action is empty to submit to the same page -->
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" required>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="rfid_uid">Card UID</label>
            <input type="text" id="rfid_uid" name="rfid_uid" required>

            <button type="submit" class="register-btn">Register Student</button>
        </form>

        <button onclick="window.location.href='index.html'" class="back-btn">Back to Home</button>
    </div>

</body>
</html>
