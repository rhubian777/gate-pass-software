<?php
// Include database connection
include("../rfid/db_connect.php");

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $student_id = $_POST["student_id"];
    $name = $_POST["name"];
    $card_uid = $_POST["card_uid"];
    $course = $_POST["course"];
    $year = $_POST["year"];

    // Prepare response array
    $response = array();
    
    // Validate input
    if (empty($student_id) || empty($name) || empty($card_uid) || empty($course) || empty($year)) {
        $response["success"] = false;
        $response["error"] = "All fields are required";
        echo json_encode($response);
        exit;
    }
    
        // Update SQL query
    $sql = "UPDATE students SET name = ?, card_uid = ?, course = ?, year = ? WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {

        // Bind parameters to include course and year
        mysqli_stmt_bind_param($stmt, "sssss", $name, $card_uid, $course, $year, $student_id);
        
        // Execute the statement
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            // Success
            $response["success"] = true;
            $response["message"] = "Student updated successfully";
        } else {
            // Error
            $response["success"] = false;
            $response["error"] = "Database error: " . mysqli_error($conn);
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        // Error in preparing statement
        $response["success"] = false;
        $response["error"] = "Statement preparation failed: " . mysqli_error($conn);
    }
    
    // Return response as JSON
    echo json_encode($response);
} else {
    // Not a POST request
    $response = array(
        "success" => false,
        "error" => "Invalid request method"
    );
    echo json_encode($response);
}

// Close database connection
mysqli_close($conn);
?>