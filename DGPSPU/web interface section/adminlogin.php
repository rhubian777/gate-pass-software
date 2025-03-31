<?php
// Start session
session_start();

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

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if honeypot field is empty (should be empty if human)
    if (empty($_POST["website"])) {
        $email = $conn->real_escape_string($_POST["email"]);
        $password = $_POST["password"];
        
        // Get admin from database
        $sql = "SELECT id, email, password FROM admins WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $row["password"])) {
                // Password is correct, set session
                $_SESSION["loggedin"] = true;
                $_SESSION["admin_id"] = $row["id"];
                $_SESSION["admin_email"] = $row["email"];
                
                // Redirect to admin dashboard
                header("location: admindashboard.php");
                exit;
            } else {
                $login_err = "Invalid email or password.";
            }
        } else {
            $login_err = "Invalid email or password.";
        }
    } else {
        // Honeypot field was filled - likely a bot
        // Silently fail without alerting the bot
        $login_err = "Invalid login attempt.";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - RFID Gate System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #86af49;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
            position: absolute;
            top: 5%;
        }
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            margin-bottom: 5px;
            display: block;
            font-weight: bold;
        }
        .honeypot {
            display: none;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .btn-login {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-login:hover {
            background-color: #45a049;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="../logos/panpacific_logo.png" alt="Panpacific University Logo">
    </div>
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if(!empty($login_err)): ?>
            <div class="error-message"><?php echo $login_err; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <!-- Honeypot field to catch bots -->
            <div class="honeypot">
                <label>Website</label>
                <input type="text" name="website">
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">Login</button>
            </div>
        </form>
    </div>
</body>
</html>
