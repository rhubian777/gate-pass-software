<?php
include("../rfid/db_connect.php"); // Database connection

// Fetch students from the database
$query = "SELECT id, student_id, name, card_uid FROM students ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="styles.css"> <!-- Your CSS file -->
    <script>
        function deleteStudent(id) {
            if (confirm("Are you sure you want to delete this student?")) {
                fetch("http://localhost/rfid/delete_student.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("✅ Student deleted successfully!");
                        document.getElementById("row-" + id).remove(); // Remove row from table
                    } else {
                        alert("❌ Error: " + data.error);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        }
    </script>
</head>
<body>

    <h2>Manage Students</h2>
    
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>RFID UID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['student_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['card_uid']; ?></td>
                    <td>
                        <button onclick="deleteStudent(<?php echo $row['id']; ?>)">❌ Delete</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <button onclick="window.location.href='add_student.php'">➕ Add Student</button>

</body>
</html>