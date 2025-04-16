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
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Time</th>
            <th>Year</th>
            <th>Course</th>
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
            <p>Lead Developer</p>
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
  </script>
</body>
</html>