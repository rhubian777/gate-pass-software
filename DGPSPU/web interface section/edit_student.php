<?php
// Include database connection
include("../rfid/db_connect.php");

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = array('success' => false);

try {
    // Check if we have the required data
    if (!isset($_POST['student_id']) || !isset($_POST['name']) || !isset($_POST['card_uid'])) {
        throw new Exception('Missing required fields');
    }
    
    // Get and sanitize the data
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $card_uid = mysqli_real_escape_string($conn, $_POST['card_uid']);
    
    // Update the student record
    $sql = "UPDATE students SET name = '$name', card_uid = '$card_uid' WHERE student_id = '$student_id'";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) > 0) {
            $response['success'] = true;
            $response['message'] = 'Student updated successfully';
        } else {
            $response['error'] = 'No changes made or student not found';
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