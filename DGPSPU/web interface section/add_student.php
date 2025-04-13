<?php
session_start();
include("../rfid/db_connect.php");

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: adminlogin.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$student_id = $student_name = $card_uid = $course = $year = "";
$success_message = $error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $student_name = trim($_POST['name']);
    $card_uid = trim($_POST['rfid_uid']);
    $course = trim($_POST['course']);
    $year = trim($_POST['year']);

    if (empty($student_id) || empty($student_name) || empty($card_uid) || empty($course) || empty($year)) {
        $error_message = "All fields are required!";
    } else {
        $check_query = "SELECT * FROM students WHERE student_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Student ID already exists!";
        } else {
            $check_query = "SELECT * FROM students WHERE card_uid = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("s", $card_uid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = "Card UID already exists!";
            } else {
                $insert_query = "INSERT INTO students (student_id, name, card_uid, course, year) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("ssssi", $student_id, $student_name, $card_uid, $course, $year);

                if ($stmt->execute()) {
                    $success_message = "Student registered successfully!";
                    $student_id = $student_name = $card_uid = $course = $year = "";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .add-student-container {
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
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
        input, select {
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
    </style>
</head>
<body>

    <div class="add-student-container">
        <h2>Add New Student</h2>

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

            <label for="course">Course</label>
            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" required>

            <label for="year">Year Level</label>
            <select id="year" name="year" required>
                <option value="">Select Year</option>
                <option value="1" <?php echo ($year == "1") ? "selected" : ""; ?>>1st Year</option>
                <option value="2" <?php echo ($year == "2") ? "selected" : ""; ?>>2nd Year</option>
                <option value="3" <?php echo ($year == "3") ? "selected" : ""; ?>>3rd Year</option>
                <option value="4" <?php echo ($year == "4") ? "selected" : ""; ?>>4th Year</option>
            </select>

            <button type="submit" class="register-btn" id="submitBtn">Register Student</button>
        </form>

        <button onclick="window.location.href='index.php'" class="back-btn">Back to Dashboard</button>
    </div>

    <script>
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Registration',
                text: 'Are you sure you want to register this student?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, register',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>
        <!--Navigation bar, at the bottom -->
                <div class="navbar">
            <a href="admindashboard.php">Admin Dashboard</a>
            <a href="delete_student.php">Student Management</a> 
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <style>
        .navbar {
            overflow: hidden;
            padding: 10px;
            text-align: center;
            position: relative;
            left: 45px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            display: inline-block;
            font-size: 16px;
            transition: 0.3s ease-in-out;
            background-color: #28a745;
            border-radius: 8px;
        }

        .navbar a:hover {
            background-color: #e74c3c; /* Green hover effect */
            border-radius: 4px;
        }

        .logout-btn {
            float: right;
            background-color: #e74c3c; /* Red logout button */
            padding: 14px 20px;
            border-radius: 8px;
        }

        .logout-btn:hover {
            background-color: #c0392b; /* Darker red on hover */
        }

        </style>
</body>
</html>
