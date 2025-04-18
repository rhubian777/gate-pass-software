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

// Check if we need to create the role column if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
if($check_column->num_rows == 0) {
    // Role column doesn't exist, add it
    $conn->query("ALTER TABLE admins ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'standard'");
    // Update the current user to superadmin
    $conn->query("UPDATE admins SET role = 'superadmin' WHERE id = {$_SESSION['admin_id']}");
}

// Get current admin's role
$stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
$stmt->bind_param("i", $_SESSION["admin_id"]);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$current_role = $admin_data['role'] ?? 'standard';
$stmt->close();

// Check if current user has permission to manage users
$can_manage_users = ($current_role == 'superadmin');

// Handle Delete Admin
if(isset($_GET['delete']) && $can_manage_users) {
    $delete_id = intval($_GET['delete']);
    
    // Don't allow deletion of the current user
    if($delete_id != $_SESSION["admin_id"]) {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if($stmt->execute()) {
            $success_msg = "Admin deleted successfully.";
        } else {
            $error_msg = "Error deleting admin.";
        }
        $stmt->close();
    } else {
        $error_msg = "You cannot delete your own account.";
    }
}

// Handle Edit Admin Role
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_admin']) && $can_manage_users) {
    $edit_id = intval($_POST['edit_id']);
    $new_role = $_POST['role'];
    
    // Validate role
    if($new_role != 'superadmin' && $new_role != 'standard') {
        $new_role = 'standard';
    }
    
    // Don't allow changing role of the current user
    if($edit_id != $_SESSION["admin_id"]) {
        $stmt = $conn->prepare("UPDATE admins SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $edit_id);
        if($stmt->execute()) {
            $success_msg = "Admin role updated successfully.";
        } else {
            $error_msg = "Error updating admin role.";
        }
        $stmt->close();
    } else {
        $error_msg = "You cannot change your own role.";
    }
}

// Process new admin form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin']) && $can_manage_users) {
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
        
        // Validate password with stronger requirements
        if(empty(trim($_POST["password"]))) {
            $password_err = "Please enter a password.";     
        } else {
            $password = trim($_POST["password"]);
            
            // Check password strength
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);
            
            if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
                $password_err = "Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.";
            }
        }
        
        // Get role
        $role = $_POST["role"] ?? 'standard';
        if($role != 'superadmin' && $role != 'standard') {
            $role = 'standard';
        }
        
        // Check input errors before inserting into database
        if(empty($email_err) && empty($password_err)) {
            // Prepare an insert statement
            $sql = "INSERT INTO admins (email, password, role) VALUES (?, ?, ?)";
            
            if($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sss", $param_email, $param_password, $param_role);
                
                // Set parameters
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                $param_role = $role;
                
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

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = " WHERE email LIKE ? ";
    $search_param = "%$search%";
    $params[] = $search_param;
    $types .= 's';
}

// Fetch all admins with search filter
$sql = "SELECT id, email, role FROM admins" . $search_condition;
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RFID Gate System</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 16px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            color: #2d3748;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        .honeypot {
            display: none;
        }

        h1 {
            color: white;
            font-size: 28px;
            margin: 0;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1a202c;
            margin-top: 0;
            font-size: 22px;
            font-weight: 600;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .student-btn {
            background-color: #3498db;
            color: white;
        }

        .student-btn:hover {
            background-color: #2980b9;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .admin-panel {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .admin-list, .add-admin {
            flex: 1;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            min-width: 350px;
        }

        .welcome-message {
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 8px;
        }

        .search-bar form {
            display: flex;
            width: 100%;
            gap: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 15px;
            width: 100%;
            background-color: white;
        }

        input:focus,
        select:focus {
            border-color: #27ae60;
            outline: none;
            box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.2);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background: #27ae60;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        tr:hover td {
            background-color: #f0fdf4;
        }

        td {
            border-bottom: 1px solid #edf2f7;
            font-size: 15px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .btn-submit {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease;
        }

        .btn-submit:hover {
            background-color: #219653;
        }

        .success-message {
            background-color: #f0fff4;
            color: #276749;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 3px solid #27ae60;
            font-weight: 500;
        }

        .error-message {
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 3px solid #e53e3e;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .edit-btn:hover {
            background-color: #2980b9;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: white;
            font-weight: 600;
            display: inline-block;
        }

        .badge-admin {
            background-color: #f39c12;
        }

        .badge-superadmin {
            background-color: #e74c3c;
        }

        .password-requirements {
            font-size: 13px;
            color: #718096;
            margin-top: 6px;
        }

        .password-meter-container {
            margin-top: 8px;
            background-color: #edf2f7;
            height: 4px;
            border-radius: 2px;
            overflow: hidden;
        }

        #password-strength {
            height: 100%;
            width: 0;
            transition: width 0.2s ease, background-color 0.2s ease;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #a0aec0;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            height: 28px;
            width: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #2d3748;
            background-color: #f7fafc;
        }

        /* Password strength meter colors */
        #password-strength.weak { background-color: #e53e3e; }
        #password-strength.fair { background-color: #f39c12; }
        #password-strength.good { background-color: #3498db; }
        #password-strength.strong { background-color: #27ae60; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px 12px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .header-buttons {
                width: 100%;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
            
            .admin-list, .add-admin {
                min-width: 100%;
                padding: 18px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .welcome-message {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ADMIN DASHBOARD</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn student-btn">Student Management</a>
                <a href="logout.php" class="btn logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="welcome-message">
            <p>Welcome, <b><?php echo htmlspecialchars($_SESSION["admin_email"]); ?></b>. 
               <?php if($current_role == 'superadmin'): ?>
                   <span class="badge badge-superadmin">Super Admin</span>
               <?php else: ?>
                   <span class="badge badge-admin">Standard Admin</span>
               <?php endif; ?>
               Manage your Admin System below.
            </p>
        </div>
        
        <?php if(!empty($success_msg)): ?>
            <div class="success-message"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error_msg)): ?>
            <div class="error-message"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="admin-panel">
            <div class="admin-list">
                <h2>Admin Users</h2>
                
                <div class="search-bar">
                    <form method="GET" action="" style="width: 100%; display: flex; gap: 8px;">
                        <input type="text" name="search" placeholder="Search by email" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn-submit">Search</button>
                        <?php if(!empty($search)): ?>
                            <a href="admindashboard.php" class="btn student-btn">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <?php if($can_manage_users): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["id"] . "</td>";
                                echo "<td>" . $row["email"] . "</td>";
                                
                                // Display role with badge
                                echo "<td>";
                                if($row["role"] == 'superadmin') {
                                    echo "<span class='badge badge-superadmin'>Super Admin</span>";
                                } else {
                                    echo "<span class='badge badge-admin'>Standard Admin</span>";
                                }
                                echo "</td>";
                                
                                // Add actions column if user can manage
                                if($can_manage_users) {
                                    echo "<td class='action-buttons'>";
                                    
                                    // Don't show edit/delete for current user
                                    if($row["id"] != $_SESSION["admin_id"]) {
                                        echo "<button onclick='openEditModal(" . $row["id"] . ", \"" . $row["email"] . "\", \"" . $row["role"] . "\")' class='edit-btn'>Edit</button>";
                                        echo "<a href='admindashboard.php?delete=" . $row["id"] . "' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this admin?\")'>Delete</a>";
                                    } else {
                                        echo "<em>Current User</em>";
                                    }
                                    
                                    echo "</td>";
                                }
                                
                                echo "</tr>";
                            }
                        } else {
                            $colspan = $can_manage_users ? 4 : 3;
                            echo "<tr><td colspan='$colspan'>No admins found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($can_manage_users): ?>
            <div class="add-admin">
                <h2>Add New Admin</h2>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                        <?php if(isset($email_err)): ?>
                            <div class="error-message"><?php echo $email_err; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" required onkeyup="checkPasswordStrength()">
                        <div class="password-meter-container">
                            <div id="password-strength"></div>
                        </div>
                        <div class="password-requirements">
                            Password must have at least 8 characters, including uppercase, lowercase, number and special character.
                        </div>
                        <?php if(isset($password_err)): ?>
                            <div class="error-message"><?php echo $password_err; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="standard">Standard Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                    <!-- Honeypot field to catch bots -->
                    <div class="honeypot">
                        <label>Website</label>
                        <input type="text" name="website">
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="add_admin" value="1">
                        <button type="submit" class="btn-submit">Add Admin</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Admin Modal -->
    <?php if($can_manage_users): ?>
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Admin Role</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Admin Email</label>
                    <input type="text" id="edit_email" readonly>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role" required>
                        <option value="standard">Standard Admin</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <input type="hidden" name="edit_admin" value="1">
                    <button type="submit" class="btn-submit">Update Role</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Password strength checker
        function checkPasswordStrength() {
            var password = document.getElementById('password').value;
            var strength = 0;
            var strengthBar = document.getElementById('password-strength');
            
            // Check lowercase letters
            if (password.match(/[a-z]+/)) {
                strength += 1;
            }
            
            // Check uppercase letters
            if (password.match(/[A-Z]+/)) {
                strength += 1;
            }
            
            // Check numbers
            if (password.match(/[0-9]+/)) {
                strength += 1;
            }
            
            // Check special characters
            if (password.match(/[^a-zA-Z0-9]+/)) {
                strength += 1;
            }
            
            // Check length
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Update strength bar
            switch(strength) {
                case 0:
                    strengthBar.style.width = "0%";
                    strengthBar.style.backgroundColor = "";
                    strengthBar.className = "";
                    break;
                case 1:
                    strengthBar.style.width = "20%";
                    strengthBar.className = "weak";
                    break;
                case 2:
                    strengthBar.style.width = "40%";
                    strengthBar.className = "fair";
                    break;
                case 3:
                    strengthBar.style.width = "60%";
                    strengthBar.className = "fair";
                    break;
                case 4:
                    strengthBar.style.width = "80%";
                    strengthBar.className = "good";
                    break;
                case 5:
                    strengthBar.style.width = "100%";
                    strengthBar.className = "strong";
                    break;
            }
        }
        
        // Edit modal functions
        var modal = document.getElementById('editModal');
        
        function openEditModal(id, email, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            modal.style.display = "block";
        }
        
        function closeEditModal() {
            modal.style.display = "none";
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
<?php
// Close connection
$conn->close();
?>