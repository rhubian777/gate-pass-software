<?php
// Include database connection
include("../rfid/db_connect.php");

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = array('success' => false);

try {
    // Check if we have the required data
    if (!isset($_POST['student_id'])) {
        throw new Exception('Missing student ID');
    }
    
    // Get and sanitize the student ID
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    
    // Delete the student record
    $sql = "DELETE FROM students WHERE student_id = '$student_id'";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            $response['success'] = true;
            $response['message'] = 'Student deleted successfully';
        } else {
            $response['error'] = 'Student not found';
        }
    } else {
        throw new Exception(mysqli_error($conn));
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Return the JSON response
echo json_encode($response);
exit;