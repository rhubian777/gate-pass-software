<?php
// Start session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: adminlogin.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your DB username
$password = ""; // Replace with your DB password
$dbname = "rfid_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process new admin form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if honeypot field is empty (should be empty if human)
    if (empty($_POST["website"])) {
        // Validate email
        if(empty(trim($_POST["email"]))) {
            $email_err = "Please enter an email.";
        } else {
            // Prepare a select statement
            $sql = "SELECT id FROM admins WHERE email = ?";
            
            if($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_email);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
                // Attempt to execute the prepared statement
                if($stmt->execute()) {
                    // Store result
                    $stmt->store_result();
                    
                    if($stmt->num_rows == 1) {
                        $email_err = "This email is already taken.";
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }
        
        // Validate password
        if(empty(trim($_POST["password"]))) {
            $password_err = "Please enter a password.";     
        } elseif(strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Check input errors before inserting into database
        if(empty($email_err) && empty($password_err)) {
            // Prepare an insert statement
            $sql = "INSERT INTO admins (email, password) VALUES (?, ?)";
            
            if($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("ss", $param_email, $param_password);
                
                // Set parameters
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                
                // Attempt to execute the prepared statement
                if($stmt->execute()) {
                    // Admin added successfully
                    $success_msg = "New admin account created successfully.";
                } else {
                    $error_msg = "Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }
    } else {
        // Honeypot field was filled - likely a bot
        // Silently fail without alerting the bot
        $error_msg = "Invalid form submission.";
    }
}

// Fetch all admins
$sql = "SELECT id, email FROM admins";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RFID Gate System</title>
    <style>
        body {
            font-family:'Times New Roman', serif;
            font-size: 19px;
            background-color:rgb(36, 168, 58);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .honeypot {
            display: none;
        }
        h1 {
            color: #333;
        }
        .header-buttons {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        .student-btn {
            background-color: #2196F3;
            color: white;
            border: none;
        }
        .student-btn:hover {
            background-color: #0b7dda;
        }
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .admin-panel {
            display: flex;
            gap: 20px;
        }
        .admin-list, .add-admin {
        flex: 1;
        background: linear-gradient(to right,rgb(98, 179, 0),rgb(107, 210, 60));
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
         background: linear-gradient(to right,rgb(180, 211, 0),rgb(165, 255, 124));
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            font-family:'Times New Roman', serif;
            font-size: 19px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn student-btn">Student Management</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="welcome-message">
            <p>Welcome, <b><?php echo htmlspecialchars($_SESSION["admin_email"]); ?></b>. Manage your Admin System below.</p>
        </div>
        
        <div class="admin-panel">
            <div class="admin-list">
                <h2>Admin Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . $row["email"] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No admins found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="add-admin">
                <h2>Add New Admin</h2>
                
                <?php if(!empty($success_msg)): ?>
                    <div class="success-message"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_msg)): ?>
                    <div class="error-message"><?php echo $error_msg; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                        <span class="error-message"><?php echo isset($email_err) ? $email_err : ''; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                        <span class="error-message"><?php echo isset($password_err) ? $password_err : ''; ?></span>
                    </div>
                    <!-- Honeypot field to catch bots -->
                    <div class="honeypot">
                        <label>Website</label>
                        <input type="text" name="website">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close connection
$conn->close();
?>