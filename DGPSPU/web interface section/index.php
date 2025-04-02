<?php
// Start session
session_start();

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: adminlogin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DGPS Panpacific</title>
  <link rel="stylesheet" href="styles.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <!-- Student Details -->
    <div class="student-container">
      <h3>STUDENT DETAILS</h3>
      <p>ID: <span id="student-id">---</span></p>
      <p>Name: <span id="student-name">---</span></p>
    </div>
 
    <!-- Scan Logs -->
    <div class="log-container">
      <div class="log-title">SCAN LOGS</div>
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="logs-table"></tbody>
        <tbody id="scan-logs-table-body">
          <!-- Logs will load here automatically -->
        </tbody>
      </table>
    </div>

    <!-- View Logs Button -->
    <button class="log-btn" onclick="toggleLogs()">View RFID Logs</button>
  </div>

  <!-- Dev team -->
  <div style="margin-top: 30px; padding: 25px 15px; border-radius: 8px; text-align: center;">
  <h3 style="text-align: center; margin-bottom: 25px; font-size: 24px; font-weight: bold; color: white;">MEET THE DEV TEAM</h3>
  
  <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px;">
    
    <!-- Member 1 -->
    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid white; border-radius: 15px; padding: 15px; background-color: hsl(120, 60%, 40%);">
      <img src="../logos/profile1.png" alt="Member 1" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #4CAF50;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: black;">Chrismar Jose Ganzagan</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: white;">Lead Developer</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
      <a href="https://www.facebook.com/JJHXCJG" target="_blank">
  <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
</a>

<a href="https://www.instagram.com/rhubian777/" target="_blank">
  <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px; margin-right: 5px;">
</a>
      </div>
    </div>
    
    <!-- Member 2 -->
    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid white; border-radius: 15px; padding: 15px; background-color: hsl(120, 60%, 40%);">
      <img src="../logos/profile2.png" alt="Member 2" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #4CAF50;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: black;">Carlitos Avel Caoayan</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: white;">Designer</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
      <a href="https://www.facebook.com/yourprofile" target="_blank">
  <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
</a>

<a href="https://www.instagram.com/yourprofile" target="_blank">
  <img src="../logos/instagram_logo.png" alt="Instagram" style="width: 20px; height: 20px; margin-right: 5px;">
</a>
      </div>
    </div>
    
    <!-- Member 3 -->
    <div style="display: flex; flex-direction: column; align-items: center; width: 200px; border: 2px solid white; border-radius: 15px; padding: 15px; background-color: hsl(120, 60%, 40%);">
      <img src="../logos/profile3.png" alt="Member 3" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; border: 3px solid #4CAF50;">
      <h4 style="margin: 0 0 5px 0; font-size: 15px; font-weight: bold; color: black;">Jonray Dale Manzano</h4>
      <p style="margin: 0 0 10px 0; font-size: 14px; color: white;">Designer</p>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
<a href="https://www.facebook.com/yourprofile" target="_blank">
  <img src="../logos/facebook_logo.png" alt="Facebook" style="width: 20px; height: 20px; margin-right: 5px;">
</a>

<a href="https://www.instagram.com/yourprofile" target="_blank">
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
                  <td>${log.time}</td>
                `;
                logsTable.appendChild(row);
              });
            } else {
              logsTable.innerHTML = '<tr><td colspan="3">No scan logs available.</td></tr>';
            }
            logContainer.style.display = 'block';
            button.textContent = 'Hide RFID Logs';
            logsVisible = true;
          })
          .catch(error => {
            console.error('Error fetching logs:', error);
            logsTable.innerHTML = '<tr><td colspan="3">Failed to load scan logs.</td></tr>';
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
            '<tr><td colspan="3">Failed to load scan logs.</td></tr>';
          console.error('Error loading logs:', error);
        });
    }

    window.onload = loadLogs;
    setInterval(loadLogs, 5000); // Refresh logs every 5 seconds
  </script>

</body>
</html>
