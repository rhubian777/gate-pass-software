<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) { 
  die(json_encode(["error" => "Database Connection Failed"]));
}

// Get the parameters correctly
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; 
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : ''; 
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';      
$course = isset($_GET['course']) ? $_GET['course'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$whereConditions = [];
$params = [];
$types = '';

if (!empty($searchTerm)) {
  $whereConditions[] = "(s.student_id LIKE ? OR s.name LIKE ?)"; 
  $searchParam = "%$searchTerm%"; 
  $params[] = $searchParam;
  $params[] = $searchParam;  
  $types .= "ss"; 
}

if (!empty($dateFrom)) {  
  $whereConditions[] = "DATE(l.timestamp) >= ?";  
  $params[] = $dateFrom;  
  $types .= "s";
}

if (!empty($dateTo)) {
  $whereConditions[] = "DATE(l.timestamp) <= ?";  
  $params[] = $dateTo;
  $types .= "s";
}

if(!empty($course)) {
  $whereConditions[] = "s.course = ?";
  $params[] = $course; 
  $types .= "s"; 
}

if(!empty($year)) {
  $whereConditions[] = "s.year = ?";
  $params[] = $year; 
  $types .= "s"; 
}

if (!empty($status)) {
  $whereConditions[] = "l.scan_type = ?";  
  $params[] = $status;
  $types .= "s";  
}

// build the where clause
$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : ""; 

// Get the scan logs with student info
$sql = "SELECT l.id as scan_id,
      l.student_id,
      l.timestamp,
      l.scan_type,
      s.name, 
      s.course, 
      s.year, 
      IFNULL(s.course, 'N/A') as course
  FROM scan_logs l
  LEFT JOIN students s ON l.student_id = s.student_id
  $whereClause
  ORDER BY l.timestamp DESC
  LIMIT 100";

$stmt = $conn->prepare($sql); 

// Parameter bind if needed
if (!empty($params)) { 
  $stmt->bind_param($types, ...$params);
}

$stmt->execute(); 
$result = $stmt->get_result(); 
$logs = []; 

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) { 
    if (!empty($row['year'])) { 
      $year = $row['year']; 
      $suffix = 'th';

      if ($year % 10 == 1 && $year % 100 != 11) { 
        $suffix = 'st'; 
      } elseif ($year % 10 == 2 && $year % 100 != 12) {
        $suffix = 'nd'; 
      } elseif ($year % 10 == 3 && $year % 100 != 13) {
        $suffix = 'rd';
      }

      $row['year'] = $year . $suffix . ' Year'; 
    }
    
    if (empty($row['course'])) {
      $row['course'] = 'N/A';
    }
    
    // Format timestamp (only if not empty)
    if (!empty($row['timestamp'])) {
      $row['timestamp'] = date('M d, Y h:i:s A', strtotime($row['timestamp']));
    }

    $logs[] = $row;
  }
}

echo json_encode($logs); 
$stmt->close();
$conn->close();
?>