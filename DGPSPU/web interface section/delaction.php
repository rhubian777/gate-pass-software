<?php
header('Content-Type: application/json');
include("../rfid/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;

    if ($student_id) {
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Query failed']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing student ID']);
    }

    $conn->close();
}
?>