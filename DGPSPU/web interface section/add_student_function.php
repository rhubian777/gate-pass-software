<?php
include 'db_connect.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['student_name']);
    $rfid_uid = trim($_POST['rfid_uid']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);

    // Check if any field is empty
    if (empty($student_id) || empty($student_name) || empty($rfid_uid) || empty($course) || empty($year)) {
        echo "<script>alert('All fields are required!'); window.history.back();</script>";
        exit;
    }

    // Prepare SQL statement to prevent SQL injection
    $sql = "INSERT INTO students (student_id, name, card_uid, course, year) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $student_id, $student_name, $rfid_uid, $course, $year);

    // Execute and check if the insertion was successful
    if ($stmt->execute()) {
        echo "<script>alert('Student registered successfully!'); window.location.href='manage_students.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>