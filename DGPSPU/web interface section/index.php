<?php
// Start session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: adminlogin.php");
    exit;
}

// Database connection for dashboard stats
$conn = new mysqli("localhost", "root", "", "rfid_system"); // Adjust credentials as needed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get dashboard stats
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalScans = $conn->query("SELECT COUNT(*) as count FROM scan_logs")->fetch_assoc()['count'];
$todayScans = $conn->query("SELECT COUNT(*) as count FROM scan_logs WHERE DATE(timestamp) = CURDATE()")->fetch_assoc()['count'];
$courseData = $conn->query("SELECT course, COUNT(*) as count FROM students GROUP BY course ORDER BY count DESC");
$yearData = $conn->query("SELECT year, COUNT(*) as count FROM students GROUP BY year ORDER BY year");

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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

  <!-- Sidebar Menu -->
  <div class="sidebar" id="sidebar">
    <div class="menu-container">
      <!-- Logo -->
      <a href="https://www.panpacificu.edu.ph/" target="_blank">
        <img src="../logos/panpacific_logo.png" alt="Logo" class="sidebar-logo"/>
      </a>

      <!-- Student Management Buttons -->
      <button id="add-student-btn">Add Student</button>
      <button id="delete-student-btn">Delete Student</button>
    </div>
  </div>

  <!-- Menu Button (Opens Sidebar) -->
  <button id="menu-btn">☰</button>
  <div class="container">
    <img src="../logos/pu_logo.png" alt="Logo" class="top-centerlogo"/>
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
        <p><strong>Last Scan:</strong> <span id="scan-time">-</span></p>
      </div>
    </div>
    <div id="scan-status"></div>
  </div>
</div>

<!-- View Logs Button -->
<button class="log-btn" onclick="toggleLogs()">View RFID Logs</button>

<!-- Scan Logs -->
<div class="log-container">
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
    <tbody id="scan-logs-table-body">
      <!-- Logs will load here automatically -->
    </tbody>
  </table>
</div>
  <!-- Dev team -->
  <div style="margin-top: 30px; padding: 25px 15px; border-radius: 8px; text-align: center;">
  <h3 style="text-align: center; margin-bottom: 25px; font-size: 24px; font-weight: bold; color: white;">MEET THE DEV TEAM</h3>
  
  <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px;">
    
    <!-- Member 1 -->

    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid #B31217; border-radius: 15px; padding: 15px; background-color: #161616; box-shadow: 0 0 10px #B31217; position: relative; overflow: hidden;">
      <img src="../logos/profile1.png" alt="Member 1" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #1E90FF; box-shadow: 0 0 15px #1E90FF; z-index: 1; position: relative;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: #D4AF37; text-shadow: 0 0 5px #D4AF37; z-index: 1;">Chrismar Jose Ganzagan</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: #1E90FF; text-shadow: 0 0 3px #1E90FF; letter-spacing: 1px; z-index: 1;">Lead Developer</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px; z-index: 1;">
        <a href="https://www.facebook.com/JJHXCJG" target="_blank">
          <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
        </a>
      </div>
    </div>

    <!-- Member 2 -->
    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid white; border-radius: 15px; padding: 15px; background-color: hsl(120, 60%, 40%);">
      <img src="../logos/profile2.png" alt="Member 2" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #4CAF50;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: black;">Carlitos Avel Caoayan</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: white;">Gay</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
      <a href="https://www.facebook.com/Toshiibonks" target="_blank">
      <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
    </a>

    <a href="https://www.instagram.com/toshirouuuuu?igsh=MWN6ZDd2MGNiZW40&utm_source=qr&fbclid=IwY2xjawJbAOBleHRuA2FlbQIxMAABHeGtJt1BGzWFZdka9bUc94PhZSd0jTKOQz0FWtu4rAvnnKV3hO5lmSC_nw_aem_iLkzQPF_TNnFw4Xe4Po8wA" target="_blank">
      <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px; margin-right: 5px;">
    </a>
      </div>
    </div>
    
    <!-- Member 3 -->
    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid white; border-radius: 15px; padding: 15px; background-color: hsl(120, 60%, 40%);">
      <img src="../logos/profile3.png" alt="Member 3" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #4CAF50;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: black;">Jonray Dale Manzano</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: white;">Arduino Case developer</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
      <a href="https://www.facebook.com/kampitsss?rdid=k5E9kQ6T8V64LZL1&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1AH9Fe1DEZ#  " target="_blank">
        <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
      </a>

      <a href="https://www.instagram.com/kampitsss?igsh=OHZhZ3Jia3FobHNt&fbclid=IwY2xjawJbAMZleHRuA2FlbQIxMAABHZsLUCcoPC4vPGY5nzw8dVC0q-ioswcbdXhwyC9d95GjJC3jMnH0fXrrDg_aem_b8iahoyExpyQuS_AXg8Fxw" target="_blank">
        <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px; margin-right: 5px;">
      </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="script.js"></script>
  <script src="back_button.js"></script>

  <script>
    // Navigation buttons
    document.getElementById("add-student-btn").addEventListener("click", () => {
      window.location.href = "add_student.php";
    });

    document.getElementById("delete-student-btn").addEventListener("click", () => {
      window.location.href = "delete_student.php";
    });
   // Toggle logs visibility
let logsVisible = false;
function toggleLogs() {
  const logContainer = document.querySelector('.log-container');
  const logsTable = document.getElementById('logs-table');
  const button = document.querySelector('.log-btn');

  if (!logsVisible) {
    fetch('fetch_logs.php')
      .then(res => res.json())
      .then(data => {
        logsTable.innerHTML = '';
        if (data.length > 0) {
          data.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${log.student_id || log.uid}</td>
              <td>${log.name || 'Unknown Student'}</td>
              <td>${log.timestamp || 'N/A'}</td>
              <td>${log.year || 'N/A'}</td>
              <td>${log.course}</td>
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
    logContainer.style.display = 'none';
    button.textContent = 'View RFID Logs';
    logsVisible = false;
  }
}

// Auto-refresh scan logs
function loadLogs() {
  fetch('load_logs.php')
    .then(res => res.text())
    .then(data => {
      document.getElementById('scan-logs-table-body').innerHTML = data;
    })
    .catch(error => {
      document.getElementById('scan-logs-table-body').innerHTML =
        '<tr><td colspan="5">Failed to load scan logs.</td></tr>';
      console.error('Error loading logs:', error);
    });
}

// Load recent activity
// Change this line in your JavaScript
function fetchLatestScan() {
  fetch('get_latest_scans.php')  // Changed from get_latest_scan.php to get_latest_scans.php
    .then(response => {
     
      const activityList = document.getElementById('recent-activity-list');
      activityList.innerHTML = '';
      
      if (data.length > 0) {
        data.forEach(activity => {
          const activityItem = document.createElement('div');
          activityItem.className = 'activity-item';
          activityItem.innerHTML = `
            <div class="activity-time">${formatTimeAgo(new Date(activity.timestamp))}</div>
            <div class="activity-content">
              <strong>${activity.name}</strong> (${activity.student_id}) scanned their card
            </div>
          `;
          activityList.appendChild(activityItem);
        });
      } else {
        activityList.innerHTML = '<div class="no-activity">No recent activity</div>';
      }
    })
    .catch(error => {
      console.error('Error loading recent activity:', error);
      document.getElementById('recent-activity-list').innerHTML = 
        '<div class="no-activity">Failed to load recent activity</div>';
    });
}

// Format time ago
function formatTimeAgo(date) {
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);
  
  if (seconds < 60) return `${seconds} seconds ago`;
  
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
  
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
  
  const days = Math.floor(hours / 24);
  return `${days} day${days > 1 ? 's' : ''} ago`;
}

// Initialize charts with PHP data
function initCharts() {
  // Course chart
  const courseCtx = document.getElementById('courseChart').getContext('2d');
  const courseChart = new Chart(courseCtx, {
    type: 'pie',
    data: {
      labels: [
        <?php 
          while($row = $courseData->fetch_assoc()) {
            echo "'" . $row['course'] . "', ";
          }
        ?>
      ],
      datasets: [{
        data: [
          <?php 
            $courseData->data_seek(0);
            while($row = $courseData->fetch_assoc()) {
              echo $row['count'] . ", ";
            }
          ?>
        ],
        backgroundColor: [
          '#4CAF50', '#2196F3', '#FFC107', '#E91E63', '#9C27B0', '#FF5722'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
  
  // Year chart
  const yearCtx = document.getElementById('yearChart').getContext('2d');
  const yearChart = new Chart(yearCtx, {
    type: 'bar',
    data: {
      labels: [
        <?php 
          while($row = $yearData->fetch_assoc()) {
            echo "'" . $row['year'] . "', ";
          }
        ?>
      ],
      datasets: [{
        label: 'Students',
        data: [
          <?php 
            $yearData->data_seek(0);
            while($row = $yearData->fetch_assoc()) {
              echo $row['count'] . ", ";
            }
          ?>
        ],
        backgroundColor: '#4CAF50'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  });
}

// Initialize everything on page load
window.onload = function() {
  loadLogs();
  loadRecentActivity();
  initCharts();
  
  // Toggle sidebar
  document.getElementById('menu-btn').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
  });
  
  // Refresh data every 30 seconds
  setInterval(loadLogs, 5000); // Refresh logs every 5 seconds
  setInterval(loadRecentActivity, 30000); // Refresh activity every 30 seconds
};

  </script>
    <script>
// Global variable to track the most recent scan ID to avoid duplicates
let lastProcessedScanId = null;

// Function to update the dashboard with student details
function updateDashboard(studentData) {
  console.log("Updating dashboard with:", studentData);
  
  document.getElementById('student-id').textContent = studentData.student_id || 'Unknown';
  document.getElementById('student-name').textContent = studentData.name || 'Unknown';
  document.getElementById('student-course').textContent = studentData.course || 'N/A';
  document.getElementById('student-year').textContent = studentData.year || 'N/A';
  document.getElementById('scan-time').textContent = studentData.formatted_time || new Date().toLocaleString();
  
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

// Function to check for new scans
function checkForNewScans() {
  console.log("Checking for new scans...");
  
  fetch('get_latest_scan.php')
    .then(response => {
      console.log("Response status:", response.status);
      return response.json();
    })
    .then(data => {
      console.log("Received data:", data);
      
      // Handle the case where "status": "empty" is returned
      if (data.status === "empty") {
        console.log("No new scans found");
        return;
      }
      
      // Since get_latest_scan.php returns an array, we take the first item (most recent scan)
      const latestScan = Array.isArray(data) ? data[0] : data;
      console.log("Latest scan:", latestScan);
      
      // Use scan_id if available, otherwise fall back to timestamp
      const scanIdentifier = latestScan.scan_id || latestScan.timestamp;
      console.log("Scan identifier:", scanIdentifier, "Last processed:", lastProcessedScanId);
      
      // Check if this is a new scan
      if (lastProcessedScanId !== scanIdentifier) {
        console.log("New scan detected!");
        lastProcessedScanId = scanIdentifier;
        updateDashboard(latestScan);
      } else {
        console.log("Already processed this scan");
      }
    })
    .catch(error => {
      console.error('Error checking for new scans:', error);
    });
}

// Poll for new scans every 2 seconds
setInterval(checkForNewScans, 2000);

// Also check immediately when the page loads
document.addEventListener('DOMContentLoaded', checkForNewScans);

    </script>

</body>
</html>