<?php include("../rfid/db_connect.php");

// Fetch all students from the database
$sql = "SELECT student_id, name, card_uid, course, year FROM students";
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
    <link rel="stylesheet" href="delete_student.css"/>
    <title>Student Management</title>
</head>
<body>
    <div class="container">
        <h2>REGISTERED STUDENTS</h2>
        
        <!-- Status message div -->
        <div id="statusMessage" class="status-message"></div>
        
        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by ID, Name...">
            <button onclick="searchStudents()">Search</button>
        </div>   <!-- Add this right after your <h2>REGISTERED STUDENTS</h2> and before the status message div -->
<div class="filter-container">
    <div class="filter-group">
        <label for="courseFilter">Filter by Course:</label>
        <select id="courseFilter" onchange="filterStudents()">
            <option value="">All Courses</option>
            <option value="BSIT">Bachelor of Science in Information Technology</option>
            <option value="BSN">Bachelor of Science in Nursing</option>
            <option value="BSP">Bachelor of Science in Pharmacy</option>
            <option value="BSCS">Bachelor of Science in Computer Science</option>
            <option value="BSCE">Bachelor of Science in Computer Engineering</option>
            <option value="BSEE">Bachelor of Science in Electrical Engineering</option>
            <option value="BSECE">Bachelor of Science in Electronics Engineering</option>
            <option value="BSCE">Bachelor of Science in Civil Engineering</option>
            <option value="BSA">Bachelor of Science in Accountancy</option>
            <option value="BSBA">Bachelor of Science in Business Administration</option>
            <option value="BSHM">Bachelor of Science in Hospitality Management</option>
            <option value="BSTM">Bachelor of Science in Tourism Management</option>
            <option value="BAP">Bachelor of Arts in Psychology</option>
            <option value="BEE">Bachelor of Elementary Education</option>
            <option value="BPA">Bachelor of Public Administration</option>
            <option value="BPE">Bachelor of Physical Education</option>
            <option value="BSE">Bachelor of Secondary Education</option>
            <option value="BSC">Bachelor of Science in Criminology</option>
            <option value="BSMT">Bachelor of Science in Marine Transportation</option>
        </select>
            </div>
            <div class="filter-group">
                <label for="yearFilter">Filter by Year:</label>
                <select id="yearFilter" onchange="filterStudents()">
                    <option value="">All Years</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <button class="reset-btn" onclick="resetFilters()">Reset Filters</button>
        </div>

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
            <th>Course</th>
            <th>Year</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="studentTableBody">
        <!-- This is the no results message row that appears in the tbody -->
        <tr id="noResultsRow" style="display: none;">
            <td colspan="6" style="text-align: center; padding: 20px;">
                <i class="fa fa-search"></i>
                <p style="margin-bottom: 20px;"> No students match your search criteria.</p>
                <button onclick="resetFilters()" class="reset-search-btn">Clear Filters</button>
            </td>
        </tr>
        
        <!-- Your regular data rows -->
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr id="row-<?= htmlspecialchars($row['student_id']) ?>">
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['card_uid']) ?></td>
                <td><?= htmlspecialchars($row['course']) ?></td>
                <td>
                    <?php 
                    // Format the year display
                    $year = trim($row['year']);
                    if (is_numeric($year)) {
                        switch ($year) {
                            case '1':
                                echo '1st Year';
                                break;
                            case '2':
                                echo '2nd Year';
                                break;
                            case '3':
                                echo '3rd Year';
                                break;
                            case '4':
                                echo '4th Year';
                                break;
                            default:
                                echo $year;
                        }
                    } else {
                        // If already in "Xst Year" format, just display it
                        echo htmlspecialchars($year);
                    }
                    ?>
                </td>
                <td>
                    <button class="btn edit-btn" onclick="openEditModal('<?= htmlspecialchars($row['student_id']) ?>', '<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= htmlspecialchars($row['card_uid']) ?>', '<?= htmlspecialchars(addslashes($row['course'])) ?>', '<?= htmlspecialchars($row['year']) ?>')">Edit</button>
                    <button class="btn delete-btn" onclick="deleteStudent('<?= htmlspecialchars($row['student_id']) ?>')">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php endif; ?>

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
            
            <div class="form-group">
                <label for="edit_course">Course:</label>
                <input type="text" id="edit_course" name="course" required>
            </div>
            
            <div class="form-group">
                <label for="edit_year">Year:</label>
                <select id="edit_year" name="year" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
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

// Function to search students
function searchStudents() {
    const searchValue = document.getElementById("searchInput").value.toLowerCase();
    const tableRows = document.querySelectorAll("#studentTableBody tr");
    
    tableRows.forEach(row => {
        const studentId = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const course = row.cells[3].textContent.toLowerCase();
        const year = row.cells[4].textContent.toLowerCase();
        
        // Check if any of the fields contain the search query
        if (studentId.includes(searchValue) || 
            name.includes(searchValue) || 
            course.includes(searchValue) || 
            year.includes(searchValue)) {
            row.style.display = ""; // Show the row
        } else {
            row.style.display = "none"; // Hide the row
        }
    });
}

// Search on enter key press
document.getElementById("searchInput").addEventListener("keyup", function(event) {
    if (event.key === "Enter") {
        searchStudents();
    }
});

// Function to delete a student
function deleteStudent(studentId) {
    console.log("Deleting student ID:", studentId);
    if (confirm("Are you sure you want to delete this student?")) {
        // Show loading in the row
        const row = document.getElementById("row-" + studentId);
        if (row) {
            const actionCell = row.cells[5]; // Updated cell index to 5
            const originalContent = actionCell.innerHTML;
            actionCell.innerHTML = '<div class="loader"></div> Deleting...';
            
            // Simple form approach instead of fetch for better compatibility
            const form = new FormData();
            form.append("student_id", studentId);
            
            // Create a new XMLHttpRequest
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "delaction.php", true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log("Server response:", xhr.responseText);
                    
                    try {
                        // Try to parse the response as JSON
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
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
                            showStatusMessage("Error: " + (response.error || "Unknown error"), "error");
                        }
                    } catch (e) {
                        console.error("Failed to parse JSON:", xhr.responseText);
                        actionCell.innerHTML = originalContent;
                        showStatusMessage("Error: Invalid server response. Please try again.", "error");
                        
                        // Refresh the page as a fallback
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                } else {
                    // HTTP error
                    actionCell.innerHTML = originalContent;
                    showStatusMessage("Server error: " + xhr.status, "error");
                    
                    // Refresh the page as a fallback
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            };
            
            xhr.onerror = function() {
                console.error("Network error");
                actionCell.innerHTML = originalContent;
                showStatusMessage("Network error occurred. The action may have completed. Refreshing page...", "error");
                
                // Refresh the page as a fallback
                setTimeout(() => {
                    location.reload();
                }, 2000);
            };
            
            // Send the request
            xhr.send(form);
        }
    }
}

// Function to open edit modal
function openEditModal(studentId, name, cardUid, course, year) {
    document.getElementById("edit_student_id").value = studentId;
    document.getElementById("edit_name").value = name;
    document.getElementById("edit_card_uid").value = cardUid;
    document.getElementById("edit_course").value = course;

    // Format numeric year into readable format
    let formattedYear = year;
    if (year === "1") formattedYear = "1st Year";
    else if (year === "2") formattedYear = "2nd Year";
    else if (year === "3") formattedYear = "3rd Year";
    else if (year === "4") formattedYear = "4th Year";

    // Set the dropdown selection
    const yearSelect = document.getElementById("edit_year");
    for (let i = 0; i < yearSelect.options.length; i++) {
        if (yearSelect.options[i].value === formattedYear) {
            yearSelect.selectedIndex = i;
            break;
        }
    }

    // Show the modal
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
    const course = document.getElementById("edit_course").value;
    const year = document.getElementById("edit_year").value;
    
    // Form validation
    if (!name.trim() || !cardUid.trim() || !course.trim()) {
        showStatusMessage("All fields are required!", "error");
        return;
    }
    
    // Change button to loading state
    const updateButton = document.getElementById("updateButton");
    const originalButtonText = updateButton.textContent;
    updateButton.innerHTML = '<div class="loader"></div> Updating...';
    updateButton.disabled = true;
    
    // Create a form data object
    const formData = new FormData();
    formData.append("student_id", studentId);
    formData.append("name", name);
    formData.append("card_uid", cardUid);
    formData.append("course", course);
    formData.append("year", year);
    
    // Use XMLHttpRequest instead of fetch
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "edit_student.php", true);
    
    xhr.onload = function() {
        // Restore button state
        updateButton.innerHTML = originalButtonText;
        updateButton.disabled = false;
        
        if (xhr.status === 200) {
            console.log("Server response:", xhr.responseText);
            
            try {
                // Try to parse the response as JSON
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Update the table row with new data
                    const row = document.getElementById("row-" + studentId);
                    if (row) {
                        row.cells[1].textContent = name;
                        row.cells[2].textContent = cardUid;
                        row.cells[3].textContent = course;
                        row.cells[4].textContent = year;
                        
                        // Update the edit button's onclick attribute
                        const editButton = row.querySelector('.edit-btn');
                        if (editButton) {
                            editButton.setAttribute('onclick', `openEditModal('${studentId}', '${name.replace(/'/g, "\\'")}', '${cardUid}', '${course.replace(/'/g, "\\'")}', '${year}')`);
                        }
                        
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
                    showStatusMessage("Error: " + (response.error || "Unknown error"), "error");
                }
            } catch (e) {
                console.error("Failed to parse JSON:", xhr.responseText);
                showStatusMessage("Error: Invalid server response. Please try again.", "error");
                console.error("JSON parse error:", e);
                console.log("Raw response:", xhr.responseText);
                
                // Refresh the page as a fallback
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        } else {
            // HTTP error
            showStatusMessage("Server error: " + xhr.status, "error");
            
            // Refresh the page as a fallback
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    };
    
    xhr.onerror = function() {
        console.error("Network error");
        updateButton.innerHTML = originalButtonText;
        updateButton.disabled = false;
        showStatusMessage("Network error occurred. The action may have completed. Refreshing page...", "error");
        
        // Refresh the page as a fallback
        setTimeout(() => {
            location.reload();
        }, 2000);
    };
    
    // Send the request
    xhr.send(formData);
});

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (event.target === modal) {
        closeEditModal();
    }
}
// Function to filter students based on selected criteria
function filterStudents() {
    const courseFilter = document.getElementById("courseFilter").value.toLowerCase();
    const yearFilter = document.getElementById("yearFilter").value;
    const tableRows = document.querySelectorAll("#studentTableBody tr:not(#noResultsRow)");
    
    let resultsFound = false;
    
    tableRows.forEach(row => {
        // Skip the noResultsRow itself
        if (row.id === "noResultsRow") return;
        
        const course = row.cells[3].textContent.toLowerCase();
        const year = row.cells[4].textContent.trim();
        
        // Check if row matches both filters
        const matchesCourse = courseFilter === "" || courseFilter === "all courses" || course.includes(courseFilter);
        const matchesYear = yearFilter === "" || yearFilter === "all years" || year === yearFilter;
        
        if (matchesCourse && matchesYear) {
            row.style.display = ""; // Show the row
            resultsFound = true;
        } else {
            row.style.display = "none"; // Hide the row
        }
    });
    
    // Show message if no results found
    const noResultsRow = document.getElementById("noResultsRow");
    if (noResultsRow) {
        if (!resultsFound && (courseFilter !== "" && courseFilter !== "all courses" || yearFilter !== "" && yearFilter !== "all years")) {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }
}

// Function to reset all filters
function resetFilters() {
    document.getElementById("courseFilter").value = "";
    document.getElementById("yearFilter").value = "";
    document.getElementById("searchInput").value = "";
    
    // Show all rows
    const tableRows = document.querySelectorAll("#studentTableBody tr:not(#noResultsRow)");
    tableRows.forEach(row => {
        row.style.display = "";
    });
    
    // Hide no results message
    const noResultsRow = document.getElementById("noResultsRow");
    if (noResultsRow) {
        noResultsRow.style.display = "none";
    }
}

// Modify your existing search function to work with filters
function searchStudents() {
    const searchValue = document.getElementById("searchInput").value.toLowerCase();
    const courseFilter = document.getElementById("courseFilter").value.toLowerCase();
    const yearFilter = document.getElementById("yearFilter").value;
    const tableRows = document.querySelectorAll("#studentTableBody tr:not(#noResultsRow)");
    
    let resultsFound = false;
    
    tableRows.forEach(row => {
        // Skip the noResultsRow
        if (row.id === "noResultsRow") return;
        
        const studentId = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const course = row.cells[3].textContent.toLowerCase();
        const year = row.cells[4].textContent.trim();
        
        // Check if search matches
        const matchesSearch = searchValue === "" || 
            studentId.includes(searchValue) || 
            name.includes(searchValue) || 
            course.includes(searchValue) || 
            year.toLowerCase().includes(searchValue);
            
        // Check if row matches filters
        const matchesCourse = courseFilter === "" || courseFilter === "all courses" || course.includes(courseFilter);
        const matchesYear = yearFilter === "" || yearFilter === "all years" || year === yearFilter;
        
        // Show row only if it matches both search and filters
        if (matchesSearch && matchesCourse && matchesYear) {
            row.style.display = ""; // Show the row
            resultsFound = true;
        } else {
            row.style.display = "none"; // Hide the row
        }
    });
    
    // Show message if no results found
    const noResultsRow = document.getElementById("noResultsRow");
    if (noResultsRow) {
        if (!resultsFound) {
            noResultsRow.style.display = "table-row";
        } else {
            noResultsRow.style.display = "none";
        }
    }
}

// Add this new function to listen for input changes on the search field
function setupSearchListeners() {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("input", function() {
            // If search input is empty, show all students
            if (this.value === "") {
                const tableRows = document.querySelectorAll("#studentTableBody tr:not(#noResultsRow)");
                tableRows.forEach(row => {
                    row.style.display = "";
                });
                
                // Hide no results message
                const noResultsRow = document.getElementById("noResultsRow");
                if (noResultsRow) {
                    noResultsRow.style.display = "none";
                }
            } else {
                // Otherwise, perform the search
                searchStudents();
            }
        });
    }
}

// Call this function when the page loads
document.addEventListener("DOMContentLoaded", function() {
    setupSearchListeners();
    
    // Add event listeners for the filters if they exist
    const courseFilter = document.getElementById("courseFilter");
    const yearFilter = document.getElementById("yearFilter");
    const resetButton = document.querySelector(".reset-filters-btn");
    
    if (courseFilter) {
        courseFilter.addEventListener("change", filterStudents);
    }
    
    if (yearFilter) {
        yearFilter.addEventListener("change", filterStudents);
    }
    
    if (resetButton) {
        resetButton.addEventListener("click", resetFilters);
    }
});
    </script>
</body>
</html>