<?php
include("../rfid/db_connect.php"); // Adjust path if needed

// Fetch all students
$query = "SELECT * FROM students";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Manage Students</h2>

    <!-- Add Student Form -->
    <form action="add_student.php" method="POST">
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="text" name="card_uid" placeholder="RFID UID" required>
        <button type="submit">Add Student</button>
    </form>

    <!-- Student List Table -->
    <table>
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
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['student_id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['card_uid'] ?></td>
                    <td>
                        <a href="manage_students.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>