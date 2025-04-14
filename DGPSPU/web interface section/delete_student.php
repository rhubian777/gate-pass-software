<?php include("../rfid/db_connect.php");

// Fetch all students from the database using prepared statement
$sql = "SELECT student_id, name, card_uid, course, year FROM students";
$result = mysqli_query($conn, $sql);

// Check for database connection errors
if (!$result) {
    $error_message = "Database error occurred. Please try again later.";
    // Log the actual error internally
    error_log("Database error: " . mysqli_error($conn));
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
        <div id="statusMessage" class="status-message" role="alert" aria-live="polite"></div>
        
        <!-- Search Bar -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by ID, Name..." aria-label="Search students">
            <button onclick="studentManager.searchStudents()">Search</button>
        </div>
        
        <!-- Filter Container -->
        <div class="filter-container">
            <div class="filter-group">
                <label for="courseFilter">Filter by Course:</label>
                <select id="courseFilter" aria-label="Filter by course">
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
                <select id="yearFilter" aria-label="Filter by year">
                    <option value="">All Years</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <button class="reset-btn" id="resetFilterBtn">Reset Filters</button>
        </div>

        <div class="table-container">
            <?php if (isset($error_message)): ?>
                <div class="status-message error" style="display: block;">
                    <?php echo htmlspecialchars($error_message); ?>
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
                        <!-- No results message row -->
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                <i class="fa fa-search"></i>
                                <p style="margin-bottom: 20px;">No students match your search criteria.</p>
                                <button id="resetSearchBtn" class="reset-search-btn">Clear Filters</button>
                            </td>
                        </tr>
                        
                        <!-- Student data rows -->
                        <?php while ($row = mysqli_fetch_assoc($result)) : 
                            $studentId = htmlspecialchars($row['student_id']);
                            $name = htmlspecialchars($row['name']);
                            $cardUid = htmlspecialchars($row['card_uid']);
                            $course = htmlspecialchars($row['course']);
                            
                            // Format the year display
                            $year = trim($row['year']);
                            $displayYear = $year;
                            if (is_numeric($year)) {
                                switch ($year) {
                                    case '1': $displayYear = '1st Year'; break;
                                    case '2': $displayYear = '2nd Year'; break;
                                    case '3': $displayYear = '3rd Year'; break;
                                    case '4': $displayYear = '4th Year'; break;
                                }
                            } else {
                                $displayYear = htmlspecialchars($year);
                            }
                        ?>
                            <tr id="row-<?= $studentId ?>" 
                                data-id="<?= $studentId ?>" 
                                data-name="<?= $name ?>" 
                                data-uid="<?= $cardUid ?>" 
                                data-course="<?= $course ?>" 
                                data-year="<?= htmlspecialchars($row['year']) ?>">
                                <td><?= $studentId ?></td>
                                <td><?= $name ?></td>
                                <td><?= $cardUid ?></td>
                                <td><?= $course ?></td>
                                <td><?= $displayYear ?></td>
                                <td>
                                    <button class="btn edit-btn" data-id="<?= $studentId ?>">Edit</button>
                                    <button class="btn delete-btn" data-id="<?= $studentId ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal" role="dialog" aria-labelledby="editModalTitle" aria-hidden="true">
        <div class="modal-content">
            <span class="close" id="closeModalBtn" aria-label="Close">&times;</span>
            <h3 id="editModalTitle">Edit Student</h3>
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
    <a href="index.php" class="back-btn" aria-label="Back to Home">← Back to Home</a>
    
    <script>
    // Student Manager Namespace/Module Pattern
    const studentManager = (function() {
        // Cache DOM elements
        const elements = {
            searchInput: document.getElementById("searchInput"),
            courseFilter: document.getElementById("courseFilter"),
            yearFilter: document.getElementById("yearFilter"),
            resetFilterBtn: document.getElementById("resetFilterBtn"),
            resetSearchBtn: document.getElementById("resetSearchBtn"),
            studentTableBody: document.getElementById("studentTableBody"),
            noResultsRow: document.getElementById("noResultsRow"),
            statusMessage: document.getElementById("statusMessage"),
            editModal: document.getElementById("editModal"),
            closeModalBtn: document.getElementById("closeModalBtn"),
            editStudentForm: document.getElementById("editStudentForm"),
            updateButton: document.getElementById("updateButton")
        };
        
        // Cached table rows
        let tableRows = [];
        
        // Initialize the module
        function init() {
            // Cache all student rows except the noResultsRow
            tableRows = Array.from(elements.studentTableBody.querySelectorAll("tr:not(#noResultsRow)"));
            
            // Set up event listeners
            setupEventListeners();
        }
        
        // Set up all event listeners
        function setupEventListeners() {
            // Search functionality
            elements.searchInput.addEventListener("input", debounce(searchStudents, 300));
            elements.searchInput.addEventListener("keyup", function(event) {
                if (event.key === "Enter") {
                    searchStudents();
                }
            });
            
            // Filter functionality
            elements.courseFilter.addEventListener("change", filterStudents);
            elements.yearFilter.addEventListener("change", filterStudents);
            elements.resetFilterBtn.addEventListener("click", resetFilters);
            
            if (elements.resetSearchBtn) {
                elements.resetSearchBtn.addEventListener("click", resetFilters);
            }
            
            // Edit student functionality
            document.querySelectorAll(".edit-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const row = document.getElementById("row-" + this.dataset.id);
                    if (row) {
                        openEditModal(
                            row.dataset.id,
                            row.dataset.name,
                            row.dataset.uid,
                            row.dataset.course,
                            row.dataset.year
                        );
                    }
                });
            });
            
            // Delete student functionality
            document.querySelectorAll(".delete-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    deleteStudent(this.dataset.id);
                });
            });
            
            // Modal functionality
            elements.closeModalBtn.addEventListener("click", closeEditModal);
            elements.editStudentForm.addEventListener("submit", handleEditSubmit);
            
            // Close modal when clicking outside
            window.addEventListener("click", function(event) {
                if (event.target === elements.editModal) {
                    closeEditModal();
                }
            });
        }
        
        // Debounce helper function for input events
        function debounce(func, delay) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }
        
        // Search students function
        function searchStudents() {
            const searchValue = elements.searchInput.value.toLowerCase();
            const courseFilter = elements.courseFilter.value.toLowerCase();
            const yearFilter = elements.yearFilter.value;
            
            let resultsFound = false;
            
            tableRows.forEach(row => {
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
            if (elements.noResultsRow) {
                elements.noResultsRow.style.display = resultsFound ? "none" : "table-row";
            }
        }
        
        // Filter students function
        function filterStudents() {
            searchStudents(); // Reuse the search function which already has filter logic
        }
        
        // Reset all filters
        function resetFilters() {
            elements.courseFilter.value = "";
            elements.yearFilter.value = "";
            elements.searchInput.value = "";
            
            // Show all rows
            tableRows.forEach(row => {
                row.style.display = "";
            });
            
            // Hide no results message
            if (elements.noResultsRow) {
                elements.noResultsRow.style.display = "none";
            }
        }
        
        // Open edit modal
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
            elements.editModal.style.display = "block";
        }
        
        // Close edit modal
        function closeEditModal() {
            elements.editModal.style.display = "none";
        }
        
        // Show status message
        function showStatusMessage(message, type) {
            elements.statusMessage.textContent = message;
            elements.statusMessage.className = "status-message " + type;
            elements.statusMessage.style.display = "block";
            
            // Accessibility announcement
            elements.statusMessage.setAttribute("role", "alert");
            
            // Hide message after 5 seconds
            setTimeout(() => {
                elements.statusMessage.style.display = "none";
            }, 5000);
        }
        
        // Delete student
        function deleteStudent(studentId) {
            if (confirm("Are you sure you want to delete this student?")) {
                // Show loading in the row
                const row = document.getElementById("row-" + studentId);
                if (row) {
                    const actionCell = row.cells[5];
                    const originalContent = actionCell.innerHTML;
                    actionCell.innerHTML = '<div class="loader"></div> Deleting...';
                    
                    // Create and send AJAX request with CSRF protection
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "delaction.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                
                                if (response.success) {
                                    // Success - remove the row with animation
                                    row.style.transition = "opacity 0.5s";
                                    row.style.opacity = "0";
                                    setTimeout(() => {
                                        row.remove();
                                        // Update the cached tableRows array
                                        tableRows = tableRows.filter(tr => tr !== row);
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
                            }
                        } else {
                            // HTTP error
                            actionCell.innerHTML = originalContent;
                            showStatusMessage("Server error: " + xhr.status, "error");
                        }
                    };
                    
                    xhr.onerror = function() {
                        actionCell.innerHTML = originalContent;
                        showStatusMessage("Network error occurred. Please try again.", "error");
                    };
                    
                    // Send the request with proper encoding
                    xhr.send("student_id=" + encodeURIComponent(studentId));
                }
            }
        }
        
        // Handle edit form submission
        function handleEditSubmit(e) {
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
            const updateButton = elements.updateButton;
            const originalButtonText = updateButton.textContent;
            updateButton.innerHTML = '<div class="loader"></div> Updating...';
            updateButton.disabled = true;
            
            // Use XHR with CSRF protection and proper encoding
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "edit_student.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            
            xhr.onload = function() {
                // Restore button state
                updateButton.innerHTML = originalButtonText;
                updateButton.disabled = false;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Update the table row with new data
                            const row = document.getElementById("row-" + studentId);
                            if (row) {
                                row.cells[1].textContent = name;
                                row.cells[2].textContent = cardUid;
                                row.cells[3].textContent = course;
                                row.cells[4].textContent = year;
                                
                                // Update the row's data attributes for future edits
                                row.dataset.name = name;
                                row.dataset.uid = cardUid;
                                row.dataset.course = course;
                                row.dataset.year = year;
                                
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
                    }
                } else {
                    // HTTP error
                    showStatusMessage("Server error: " + xhr.status, "error");
                }
            };
            
            xhr.onerror = function() {
                updateButton.innerHTML = originalButtonText;
                updateButton.disabled = false;
                showStatusMessage("Network error occurred. Please try again.", "error");
            };
            
            // Send the request with proper encoding
            const params = 
                "student_id=" + encodeURIComponent(studentId) + "&" +
                "name=" + encodeURIComponent(name) + "&" +
                "card_uid=" + encodeURIComponent(cardUid) + "&" +
                "course=" + encodeURIComponent(course) + "&" +
                "year=" + encodeURIComponent(year);
                
            xhr.send(params);
        }
        
        // Public API
        return {
            init: init,
            searchStudents: searchStudents,
            filterStudents: filterStudents,
            resetFilters: resetFilters
        };
    })();

    // Initialize when DOM is fully loaded
    document.addEventListener("DOMContentLoaded", function() {
        studentManager.init();
    });
    </script>
</body>
</html>