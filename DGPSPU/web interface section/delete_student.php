    <?php
    include("../rfid/db_connect.php");

    // Fetch all students from the database
    $sql = "SELECT student_id, name, card_uid FROM students";
    $result = mysqli_query($conn, $sql);
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>
    <link rel="stylesheet" href="delete_student.css"> <!-- Isolated CSS -->
    <script>
        function deleteStudent(studentId) {
        console.log("🛠️ Deleting student ID:", studentId);
        if (confirm("Are you sure you want to delete this student?")) {
            fetch("delaction.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "student_id=" + encodeURIComponent(studentId)
            })
            .then(response => response.json())
            .then(data => {
            console.log("🔄 Server response:", data);
            if (data.success) {
                alert("✅ Student deleted successfully!");
                const row = document.getElementById("row-" + studentId);
                if (row) row.remove();
            } else {
                alert("❌ Error: " + data.error);
            }
            })
            .catch(error => {
            console.error("🚨 Fetch error:", error);
            });
        }
        }

    </script>
    </head>
    <body class="delete-student-page">

    <div class="container">
        <h2>REGISTERED STUDENTS</h2>

        <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Card UID</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr id="row-<?= htmlspecialchars($row['student_id']) ?>">
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['card_uid']) ?></td>
                <td>
                    <button class="delete-btn" onclick="deleteStudent('<?= htmlspecialchars($row['student_id']) ?>')">Delete</button>
                </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Fixed Back to Home Button -->
    <a href="index.html" class="back-btn">← Back to Home</a>

    </body>
    </html>

    <?php mysqli_close($conn); ?>