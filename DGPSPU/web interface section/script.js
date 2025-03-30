document.getElementById("menu-btn").addEventListener("click", function() {
  let dropdown = document.querySelector(".dropdown");
  dropdown.classList.toggle("show");
  
});

// Fetch Scan Logs from PHP
function fetchLogs() {
  fetch("../rfid/fetch_logs.php")
      .then(response => {
          if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          console.log("Logs fetched:", data);
          let logsTable = document.getElementById("logs-table");
          logsTable.innerHTML = "";
          data.forEach(log => {
              logsTable.innerHTML += `
                  <tr>
                      <td>${log.student_id}</td>
                      <td>${log.name}</td>
                      <td>${log.timestamp}</td>
                  </tr>
              `;
          });
      })
      .catch(error => console.error("Fetch error:", error));
}

// Add Student Function
function addStudent() {
  let studentID = prompt("Enter Student ID:");
  let studentName = prompt("Enter Student Name:");

  if (studentID && studentName) {
      fetch("../rfid/add_student.php", { // Updated path
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `student_id=${studentID}&name=${studentName}`
      })
      .then(response => response.json())
      .then(data => {
          alert(data.message);
          fetchLogs(); // Refresh logs
      })
      .catch(error => console.error("Error adding student:", error));
  }
}

// Delete Student Function
function deleteStudent() {
  let studentID = prompt("Enter Student ID to Delete:");

  if (studentID) {
      fetch("../rfid/delete_student.php", { // Updated path
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `student_id=${studentID}`
      })
      .then(response => response.json())
      .then(data => {
          alert(data.message);
          fetchLogs(); // Refresh logs
      })
      .catch(error => console.error("Error deleting student:", error));
  }
}
