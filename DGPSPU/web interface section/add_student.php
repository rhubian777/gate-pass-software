<?php
session_start(); // Start session for potential admin check
include("../rfid/db_connect.php"); // Database connection

// Check if user is logged in as admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: adminlogin.php");
    exit;
}

// Enable error reporting for debugging (consider removing in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$student_id = $student_name = $card_uid = "";
$success_message = $error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use prepared statements to prevent SQL injection
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['name']);
    $card_uid = trim($_POST['rfid_uid']);

    // Check if fields are empty
    if (empty($student_id) || empty($student_name) || empty($card_uid)) {
        $error_message = "All fields are required!";
    } else {
        // Check if student ID already exists
        $check_query = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Student ID already exists!";
        } else {
            // Check if card UID already exists
            $check_query = "SELECT * FROM students WHERE card_uid = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("s", $card_uid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "Card UID already exists!";
            } else {
                // Insert into database using prepared statement
                $insert_query = "INSERT INTO students (student_id, name, card_uid) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sss", $student_id, $student_name, $card_uid);
                
                if ($stmt->execute()) {
                    $success_message = "Student registered successfully!";
                    // Clear form data after successful submission
                    $student_id = $student_name = $card_uid = "";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - RFID System</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Include SweetAlert2 -->
    <style>
        /* Additional styles if needed */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .add-student-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: bold;
        }
        input {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .register-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .register-btn:hover {
            background-color: #45a049;
        }
        .back-btn {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .back-btn:hover {
            background-color: #0b7dda;
        }
            .page-header {
        display: flex;
        justify-content: flex-end; /* Move buttons to the right */
        align-items: center;
        padding: 10px 20px;
        background-color: transparent; /* Remove background */
         }

    .header-buttons {
        display: flex;
        gap: 10px;
       }

    .header-btn {
        background-color: #2196F3;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        transition: background-color 0.3s ease, color 0.3s ease; /* Smooth hover effect */
        }

    .header-btn:hover {
        background-color: #03C03C; /* Darker blue on hover */
        color: #fff;
      }

    /* Logout button hover effect */
    .logout-btn {
        background-color: #4CAF50; /* Green default */
      }

    .logout-btn:hover {
        background-color: #f44336; /* Turns red on hover */
        color: white;
     }


    </style>
</head>
<body>
    <!-- Header with navigation -->
    <div class="page-header">
        <div class="header-buttons">
            <a href="index.php" class="header-btn">Student Management</a>
            <a href="admindashboard.php" class="header-btn">Admin Management</a>
            <a href="logout.php" class="header-btn logout-b tn">Logout</a>
        </div>
    </div>

    <div class="add-student-container">
        <h2>Add New Student</h2>
        
        <!-- Show any error messages -->
        <?php if (!empty($error_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $error_message; ?>',
                    confirmButtonText: 'OK'
                });
            });
        </script>
        <?php endif; ?>
        
        <!-- Show success message -->
        <?php if (!empty($success_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo $success_message; ?>',
                    confirmButtonText: 'OK'
                });
            });
        </script>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="studentForm">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required>

            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student_name); ?>" required>

            <label for="rfid_uid">Card UID</label>
            <input type="text" id="rfid_uid" name="rfid_uid" value="<?php echo htmlspecialchars($card_uid); ?>" required>

            <button type="submit" class="register-btn" id="submitBtn">Register Student</button>
        </form>

        <button onclick="window.location.href='index.php'" class="back-btn">Back to Dashboard</button>
    </div>

    <script>
        // Add confirmation dialog before form submission
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            Swal.fire({
                title: 'Confirm Registration',
                text: 'Are you sure you want to register this student?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, register',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed, submit the form
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>