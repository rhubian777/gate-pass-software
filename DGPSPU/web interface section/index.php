<?php

// Now start the session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: adminlogin.php");
    exit;
}

// Database connection function
function getDbConnection() {
    // Move these to a configuration file in production
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "rfid_system";
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($conn->connect_error) {
        // Log error properly instead of exposing it
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    return $conn;
}

// Get dashboard stats using prepared statements where applicable
function getDashboardStats() {
    $stats = [
        'totalStudents' => 0,
        'totalScans' => 0,
        'todayScans' => 0,
        'courseData' => [],
        'yearData' => []
    ];
    
    $conn = getDbConnection();
    if (!$conn) return $stats;
    
    // Get total students
    $result = $conn->query("SELECT COUNT(*) as count FROM students");
    if ($result) {
        $stats['totalStudents'] = $result->fetch_assoc()['count'];
    }
    
    // Get total scans
    $result = $conn->query("SELECT COUNT(*) as count FROM scan_logs");
    if ($result) {
        $stats['totalScans'] = $result->fetch_assoc()['count'];
    }
    
    // Get today's scans
    $result = $conn->query("SELECT COUNT(*) as count FROM scan_logs WHERE DATE(timestamp) = CURDATE()");
    if ($result) {
        $stats['todayScans'] = $result->fetch_assoc()['count'];
    }
    
    // Get course data
    $result = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course ORDER BY count DESC");
    if ($result) {
        $courseData = [];
        while ($row = $result->fetch_assoc()) {
            $courseData[] = $row;
        }
        $stats['courseData'] = $courseData;
    }
    
    // Get year data
    $result = $conn->query("SELECT year, COUNT(*) as count FROM students GROUP BY year ORDER BY year");
    if ($result) {
        $yearData = [];
        while ($row = $result->fetch_assoc()) {
            $yearData[] = $row;
        }
        $stats['yearData'] = $yearData;
    }
    
    $conn->close();
    return $stats;
}

// Get dashboard statistics
$dashboardStats = getDashboardStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DGPS Panpacific</title>
  <link rel="stylesheet" href="styles.css"/>
  <link rel="stylesheet" href="dashboard.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

  <!-- Sidebar Menu -->
  <div class="sidebar" id="sidebar">
    <div class="menu-container">

      <!-- Logo -->
      <a href="https://www.panpacificu.edu.ph/" target="_blank">
        <img src="../logos/panpacific_logo.png" alt="Panpacific University Logo" class="sidebar-logo"/>
      </a>

      <!-- Student Management Buttons -->
      <button id="add-student-btn">Add Student</button>
      <button id="delete-student-btn">Delete Student</button>
      
      <!-- Added logout button -->
      <button id="logout-btn">Logout</button>
    </div>
  </div>

  <!-- Menu Button (Opens Sidebar) -->
  <button id="menu-btn" aria-label="Open menu">☰</button>
  
  <div class="container">
    <img src="../logos/pu_logo.png" alt="PU Logo" class="top-centerlogo"/>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
      <div class="dashboard-card">
        <h2>STUDENT DASHBOARD</h2>
        <div id="dashboard-content">
          <div class="dashboard-info">
            <p><strong>Student ID:</strong> <span id="student-id">Waiting for scan...</span></p>
            <p><strong>Name:</strong> <span id="student-name">Waiting for scan...</span></p>
            <p><strong>Course:</strong> <span id="student-course">Waiting for scan...</span></p>
            <p><strong>Year:</strong> <span id="student-year">Waiting for scan...</span></p>
          </div>
          <div class="info-row">
            <span class="label">In:</span>
            <span id="time-in">Not available</span>
          </div>
          <div class="info-row">
            <span class="label">Out:</span>
            <span id="time-out">Not available</span>
          </div>
        </div>
        <div id="scan-status" aria-live="polite"></div>
      </div>
    </div>

    <!-- View Logs Button -->
    <button class="log-btn" id="toggle-logs-btn">View RFID Logs</button>

    <!-- Scan Logs -->
    <div class="log-container" id="logs-container">
      <div class="log-title">SCAN LOGS</div>
      
      <div class="search-filter-container">
        <div class="search-box">
            <input type="text" id="search-input" placeholder="Search by ID or name...">
            <button id="search-btn">Search</button>
            <button id="clear-search-btn">Clear</button>
        </div>
        <div class="advanced-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="date-from">From:</label>
                    <input type="date" id="date-from">
                </div>
                <div class="filter-group">
                    <label for="date-to">To:</label>
                    <input type="date" id="date-to">
                </div>
                <div class="filter-group">
                    <label for="course-filter">Course:</label>
                    <select id="course-filter">
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
                    <option value="BSMT">Bachelor of Science in Marine Transportation</option>>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="year-filter">Year:</label>
                    <select id="year-filter">
                        <option value="">All Years</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter">
                        <option value="">All</option>
                        <option value="IN">IN</option>
                        <option value="OUT">OUT</option>
                    </select>
                </div>
            </div>
            <button id="apply-filters-btn">Apply Filters</button>
        </div>
      </div>
      
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Time</th>
            <th>Year</th>
            <th>Course</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody id="logs-table"></tbody>
      </table>
    </div>

    <!-- Dev team -->
    <div class="dev-team-section">
      <h3>MEET THE DEV TEAM</h3>
      <div class="team-members">
        
        <!-- Member 1 -->
        <div class="member">
          <div class="member-img-container">
            <img src="../logos/profile1.png" alt="Chrismar Jose Ganzagan" class="member-img">
          </div>
          <div class="member-info">
            <h4>Chrismar Jose Ganzagan</h4>
            <p>What's up Homie, I'm tony</p>
            <div class="social-links">
              <a href="https://www.facebook.com/JJHXCJG" target="_blank">
                <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px;">
              </a>
            </div>
          </div>
        </div>

        <!-- Member 2 -->
        <div class="member">
          <div class="member-img-container">
            <img src="../logos/profile2.png" alt="Carlitos Avel Caoayan" class="member-img">
          </div>
          <div class="member-info">
            <h4>Carlitos Avel Caoayan</h4>
            <p>*</p>
            <div class="social-links">
              <a href="https://www.facebook.com/Toshiibonks" target="_blank">
                <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px;">
              </a>
              <a href="https://www.instagram.com/toshirouuuuu" target="_blank">
                <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px;">
              </a>
            </div>
          </div>
        </div>

        <!-- Member 3 -->
        <div class="member">
          <div class="member-img-container">
            <img src="../logos/profile3.png" alt="Jonray Dale Manzano" class="member-img">
          </div>
          <div class="member-info">
            <h4>Jonray Dale Manzano</h4>
            <p>*</p>
            <div class="social-links">
              <a href="https://www.facebook.com/kampitsss" target="_blank">
                <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px;">
              </a>
              <a href="https://www.instagram.com/kampitsss" target="_blank">
                <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px;">
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
  <script>
   // Global variables
let logsVisible = false;
let lastProcessedScanId = null;

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
  // Initialize components
  initSidebar();
  initLogViewer();
  
  // Start periodic checks
  checkForNewScans();
  checkForInOutTimes();
  
  // Set up intervals for periodic updates
  setInterval(checkForNewScans, 2000);
  setInterval(checkForInOutTimes, 2000);
  
  // Add logout functionality
  document.getElementById("logout-btn").addEventListener("click", function() {
    window.location.href = "logout.php";
  });
});

// Initialize sidebar with improved animation
function initSidebar() {
  // Add Student button
  document.getElementById("add-student-btn").addEventListener("click", () => {
    window.location.href = "add_student.php";
  });
  
  // Delete Student button
  document.getElementById("delete-student-btn").addEventListener("click", () => {
    window.location.href = "delete_student.php";
  });
  
  // Create overlay element for sidebar
  const overlay = document.createElement('div');
  overlay.className = 'sidebar-overlay';
  document.body.appendChild(overlay);
  
  // Menu toggle button
  document.getElementById('menu-btn').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
    overlay.classList.toggle('active');
    this.classList.toggle('active');
  });
  
  // Close sidebar when clicking overlay
  overlay.addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.remove('show');
    overlay.classList.remove('active');
    document.getElementById('menu-btn').classList.remove('active');
  });
}

// Initialize log viewer functionality
function initLogViewer() {
  document.getElementById('toggle-logs-btn').addEventListener('click', toggleLogs);
}

// Toggle logs visibility
function toggleLogs() {
  const logContainer = document.getElementById('logs-container');
  const logsTable = document.getElementById('logs-table');
  const button = document.getElementById('toggle-logs-btn');
  
  if (!logsVisible) {
    // Show logs
    fetch('fetch_logs.php')
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        logsTable.innerHTML = '';
        if (data && data.length > 0) {
          data.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${log.student_id || log.uid || 'N/A'}</td>
              <td>${log.name || 'Unknown Student'}</td>
              <td>${log.timestamp || 'N/A'}</td>
              <td>${log.year || 'N/A'}</td>
              <td>${log.course || 'N/A'}</td>
              <td><span class="status-${(log.scan_type || 'unknown').toLowerCase()}">${log.scan_type || 'N/A'}</span></td>
            `;
            logsTable.appendChild(row);
          });
        } else {
          logsTable.innerHTML = '<tr><td colspan="5">No scan logs available.</td></tr>';
        }
        logContainer.style.display = 'block';
        button.textContent = 'Hide RFID Logs';
        logsVisible = true;
      })
      .catch(error => {
        console.error('Error fetching logs:', error);
        logsTable.innerHTML = '<tr><td colspan="5">Failed to load scan logs.</td></tr>';
        logContainer.style.display = 'block';
        button.textContent = 'Hide RFID Logs';
        logsVisible = true;
      });
  } else {
    // Hide logs
    logContainer.style.display = 'none';
    button.textContent = 'View RFID Logs';
    logsVisible = false;
  }
}

// Function to check for new scans
function checkForNewScans() {
  fetch('get_latest_scan.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Handle empty response
      if (data.status === "empty") {
        return;
      }
      
      // Process latest scan data
      const latestScan = Array.isArray(data) ? data[0] : data;
      const scanIdentifier = latestScan.scan_id || latestScan.timestamp;
      
      // Update dashboard if new scan detected
      if (lastProcessedScanId !== scanIdentifier) {
        lastProcessedScanId = scanIdentifier;
        updateDashboard(latestScan);
      }
    })
    .catch(error => {
      console.error('Error checking for new scans:', error);
    });
}

// Function to update dashboard with student data
function updateDashboard(studentData) {
  // Using safe display with escaping for security
  document.getElementById('student-id').textContent = (studentData.student_id || 'Unknown');
  document.getElementById('student-name').textContent = (studentData.name || 'Unknown');
  document.getElementById('student-course').textContent = (studentData.course || 'N/A');
  document.getElementById('student-year').textContent = (studentData.year || 'N/A');
  
  // Show scan status
  const scanStatus = document.getElementById('scan-status');
  scanStatus.textContent = 'Scan Successful!';
  scanStatus.className = 'success';
  
  // Clear the status after 3 seconds
  setTimeout(() => {
    scanStatus.textContent = '';
    scanStatus.className = '';
  }, 3000);
}

// Function to check for time in/out updates
function checkForInOutTimes() {
  fetch('time_in_out.php')
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Handle empty response
      if (data.status === "empty") {
        return;
      }
      
      // Update dashboard with time in/out data
      updateTimeInOut(data);
    })
    .catch(error => {
      console.error('Error checking for in/out times:', error);
    });
}

// Function to update time in/out display
function updateTimeInOut(studentData) {
  // Only update student info if we have valid data
  if (studentData.student_id) {
    document.getElementById('student-id').textContent = studentData.student_id;
    document.getElementById('student-name').textContent = (studentData.name || 'Unknown');
    document.getElementById('student-course').textContent = (studentData.course || 'N/A');
    document.getElementById('student-year').textContent = (studentData.year || 'N/A');
  }
  
  // Update time in
  if (studentData.time_in && studentData.time_in !== 'Not available') {
    document.getElementById('time-in').textContent = studentData.time_in;
  }
  
  // Update time out
  if (studentData.time_out && studentData.time_out !== 'Not available') {
    document.getElementById('time-out').textContent = studentData.time_out;
  }
  
  // Show status update if both fields have changed
  if ((studentData.time_in && studentData.time_in !== 'Not available') || 
      (studentData.time_out && studentData.time_out !== 'Not available')) {
    
    // Show scan status
    const scanStatus = document.getElementById('scan-status');
    scanStatus.textContent = 'Time in & Out Updated!';
    scanStatus.className = 'success';
    
    // Clear the status after 3 seconds
    setTimeout(() => {
      scanStatus.textContent = '';
      scanStatus.className = '';
    }, 3000);
  }
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and filter elements
    const searchBtn = document.getElementById('search-btn');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    
    // Search button click event
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            performSearch();
        });
    }
    
    // Clear search button click event
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            clearSearch();
        });
    }
    
    // Apply filters button click event
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            performSearch();
        });
    }
    
    // Allow search on enter key press
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
});

// Perform search with all filters
function performSearch() {
    const searchTerm = document.getElementById('search-input').value.trim();
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    const course = document.getElementById('course-filter').value;
    const year = document.getElementById('year-filter').value;
    const status = document.getElementById('status-filter').value;
    
    // Build query string
    let queryParams = [];
    if (searchTerm !== '') queryParams.push(`search=${encodeURIComponent(searchTerm)}`);
    if (dateFrom !== '') queryParams.push(`date_from=${encodeURIComponent(dateFrom)}`);
    if (dateTo !== '') queryParams.push(`date_to=${encodeURIComponent(dateTo)}`);
    if (course !== '') queryParams.push(`course=${encodeURIComponent(course)}`);
    if (year !== '') queryParams.push(`year=${encodeURIComponent(year)}`);
    if (status !== '') queryParams.push(`status=${encodeURIComponent(status)}`);
    
    const queryString = queryParams.length > 0 ? `?${queryParams.join('&')}` : '';
    
    // Fetch search results
    fetch(`search_logs.php${queryString}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateLogsTable(data);
        })
        .catch(error => {
            console.error('Error searching logs:', error);
            document.getElementById('logs-table').innerHTML = 
                '<tr><td colspan="6">Error searching logs. Please try again.</td></tr>';
        });
}

// Clear search inputs and reset to default view
function clearSearch() {
    // Clear input fields
    document.getElementById('search-input').value = '';
    document.getElementById('date-from').value = '';
    document.getElementById('date-to').value = '';
    document.getElementById('course-filter').value = '';
    document.getElementById('year-filter').value = '';
    document.getElementById('status-filter').value = '';
    
    // Reset to default view
    fetch('fetch_logs.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateLogsTable(data);
        })
        .catch(error => {
            console.error('Error fetching logs:', error);
        });
}

// Update logs table with search results
function updateLogsTable(data) {
    const logsTable = document.getElementById('logs-table');
    
    // Clear current table content
    logsTable.innerHTML = '';
    
    if (data && data.length > 0) {
        data.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${log.student_id || 'N/A'}</td>
                <td>${log.name || 'Unknown Student'}</td>
                <td>${log.timestamp || 'N/A'}</td>
                <td>${log.year || 'N/A'}</td>
                <td>${log.course || 'N/A'}</td>
                <td><span class="status-${(log.scan_type || 'unknown').toLowerCase()}">${log.scan_type || 'N/A'}</span></td>
            `;
            logsTable.appendChild(row);
        });
    } else {
        logsTable.innerHTML = '<tr><td colspan="6">No matching records found.</td></tr>';
    }
}
  </script>
</body>
</html>