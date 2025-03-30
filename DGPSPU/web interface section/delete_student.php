<?php include("../rfid/db_connect.php"); 

// Fetch all students from the database
$sql = "SELECT student_id, name, card_uid FROM students";
$result = mysqli_query($conn, $sql);

// Check for database connection errors
if (!$result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <style>
        /* Improved CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        h2 {
            color: #333;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #333;
        }
        
        tr:hover {
            background-color: #f7f7f7;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .delete-btn {
            background-color: #ff5252;
            color: white;
            margin-right: 5px;
        }
        
        .delete-btn:hover {
            background-color: #ff0000;
        }
        
        .edit-btn {
            background-color: #4caf50;
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #388e3c;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2196f3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            font-weight: 500;
        }
        
        .back-btn:hover {
            background-color: #0b7dda;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 100px auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background-color: #388e3c;
        }
        
        /* Status message */
        .status-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            display: none;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Loader */
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid #3498db;
            animation: spin 1s linear infinite;
            vertical-align: middle;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>REGISTERED STUDENTS</h2>
        
        <!-- Status message div -->
        <div id="statusMessage" class="status-message"></div>
        
        <div class="table-container">
            <?php if (isset($error_message)): ?>
                <div class="status-message error" style="display: block;">
                    <?php echo $error_message; ?>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Card UID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr id="row-<?= htmlspecialchars($row['student_id']) ?>">
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['card_uid']) ?></td>
                                <td>
                                    <button class="btn edit-btn" onclick="openEditModal('<?= htmlspecialchars($row['student_id']) ?>', '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['card_uid']) ?>')">Edit</button>
                                    <button class="btn delete-btn" onclick="deleteStudent('<?= htmlspecialchars($row['student_id']) ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Student</h3>
            <form id="editStudentForm">
                <input type="hidden" id="edit_student_id" name="student_id">
                
                <div class="form-group">
                    <label for="edit_name">Student Name:</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_card_uid">Card UID:</label>
                    <input type="text" id="edit_card_uid" name="card_uid" required>
                </div>
                
                <button type="submit" class="submit-btn" id="updateButton">Update Student</button>
            </form>
        </div>
    </div>
    
    <!-- Fixed Back to Home Button -->
    <a href="index.php" class="back-btn">← Back to Home</a>
    
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded");
        });
        
        // Function to delete a student
        function deleteStudent(studentId) {
            console.log("Deleting student ID:", studentId);
            if (confirm("Are you sure you want to delete this student?")) {
                // Show loading in the row
                const row = document.getElementById("row-" + studentId);
                if (row) {
                    const actionCell = row.cells[3];
                    const originalContent = actionCell.innerHTML;
                    actionCell.innerHTML = '<div class="loader"></div> Deleting...';
                    
                    // Perform the deletion
                    fetch("delaction.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "student_id=" + encodeURIComponent(studentId)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Server returned status " + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Server response:", data);
                        if (data.success) {
                            // Success - remove the row with animation
                            row.style.transition = "opacity 0.5s";
                            row.style.opacity = "0";
                            setTimeout(() => {
                                row.remove();
                                showStatusMessage("Student deleted successfully!", "success");
                            }, 500);
                        } else {
                            // Error - restore the row and show error
                            actionCell.innerHTML = originalContent;
                            showStatusMessage("Error: " + data.error, "error");
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        // Restore the row content
                        actionCell.innerHTML = originalContent;
                        showStatusMessage("Network error occurred. Please try again.", "error");
                    });
                }
            }
        }
        
        // Function to open edit modal
        function openEditModal(studentId, name, cardUid) {
            document.getElementById("edit_student_id").value = studentId;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_card_uid").value = cardUid;
            document.getElementById("editModal").style.display = "block";
        }
        
        // Function to close edit modal
        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }
        
        // Function to show status message
        function showStatusMessage(message, type) {
            const statusElement = document.getElementById("statusMessage");
            statusElement.textContent = message;
            statusElement.className = "status-message " + type;
            statusElement.style.display = "block";
            
            // Hide message after 5 seconds
            setTimeout(() => {
                statusElement.style.display = "none";
            }, 5000);
        }
        
        // Edit student form submission
        document.getElementById("editStudentForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const studentId = document.getElementById("edit_student_id").value;
            const name = document.getElementById("edit_name").value;
            const cardUid = document.getElementById("edit_card_uid").value;
            
            // Form validation
            if (!name.trim() || !cardUid.trim()) {
                showStatusMessage("All fields are required!", "error");
                return;
            }
            
            // Change button to loading state
            const updateButton = document.getElementById("updateButton");
            const originalButtonText = updateButton.textContent;
            updateButton.innerHTML = '<div class="loader"></div> Updating...';
            updateButton.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append("student_id", studentId);
            formData.append("name", name);
            formData.append("card_uid", cardUid);
            
            // Send data to server
            fetch("edit_student.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Server returned status " + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Restore button state
                updateButton.innerHTML = originalButtonText;
                updateButton.disabled = false;
                
                if (data.success) {
                    // Update the table row with new data
                    const row = document.getElementById("row-" + studentId);
                    if (row) {
                        row.cells[1].textContent = name;
                        row.cells[2].textContent = cardUid;
                        // Highlight the updated row
                        row.style.transition = "background-color 1s";
                        row.style.backgroundColor = "#d4edda";
                        setTimeout(() => {
                            row.style.backgroundColor = "";
                        }, 2000);
                    }
                    
                    showStatusMessage("Student updated successfully!", "success");
                    closeEditModal();
                } else {
                    showStatusMessage("Error: " + data.error, "error");
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                // Restore button state
                updateButton.innerHTML = originalButtonText;
                updateButton.disabled = false;
                showStatusMessage("Network error occurred. Please try again.", "error");
            });
        });
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById("editModal");
            if (event.target === modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>