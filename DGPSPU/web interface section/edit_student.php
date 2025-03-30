<?php
// Include database connection
include("../rfid/db_connect.php");

// Set content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $card_uid = isset($_POST['card_uid']) ? $_POST['card_uid'] : '';
    
    // Validate the data
    if (empty($student_id) || empty($name) || empty($card_uid)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required']);
        exit;
    }
    
    // Check if card UID already exists for another student
    $check_sql = "SELECT student_id FROM students WHERE card_uid = ? AND student_id != ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ss", $card_uid, $student_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo json_encode(['success' => false, 'error' => 'Card UID already assigned to another student']);
        exit;
    }
    
    // Prepare the SQL statement to update the student
    $sql = "UPDATE students SET name = ?, card_uid = ? WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // Bind parameters and execute
        mysqli_stmt_bind_param($stmt, "sss", $name, $card_uid, $student_id);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
mysqli_close($conn);
?>
