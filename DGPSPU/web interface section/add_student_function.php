<?php
include 'db_connect.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $rfid_uid = $_POST['rfid_uid'];

    // Insert student data into the database
    $sql = "INSERT INTO students (student_id, name, card_uid) VALUES ('$student_id', '$student_name', '$rfid_uid')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Student registered successfully!'); window.location.href='manage_students.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }

    mysqli_close($conn);
}
?>