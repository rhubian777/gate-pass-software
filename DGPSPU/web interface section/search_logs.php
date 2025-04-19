<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "rfid_system");

if ($conn->connect_error) { 
  die(json_encode(["error" => "Database Connection Failed"]));

}

// Get the parameters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : ''; 
$dateForm = isset($_GET['search']) ? $_GET['date_form'] : '';
$dateTo = isset($_GET['search']) ? $_GET['date_to'] : '';
$course = isset($_GET['search']) ? $_GET['course'] : '';
$year = isset($_GET['search']) ? $_GET['year'] : '';
$status = isset($_GET['search']) ? $_GET['status'] : '';

$whereConditions = [];
$params = [];
$types = [];

if (!empty($searchTerm)) {
  $whereConditions[] = "(s.student_id LIKE ? OR s.name LIKE ?)"; 
  $searchParam = "%$searchTerm%"; 
  $params[] = $searchParam;
  $params[] = $searcgParam;
  $types .= "ss"; 
}

if (!empty($dateForm)) {
  $whereConditions[] = "DATE(1.timestamp) >= ?"; 
  $params[] = $dateForm; 
  $types .= "s";
}

if (!empty($dateTo)) {
  $whereConditions[] = "DATE(1.timestamp) >= ?";
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
  $whereConditions[] = "1.scan_type = ?"; 
  $params[] = $status;
  $status .= "s";
}

// build the where clause
$whereClause = !empty($whereConditions) ? "Where " .implode(" AND ", $whereConditions) : ""; 

// Get the scan logs with student info
$sql = "SELECT 1.id as scan_id,
      1.student_id,
      1.timestamp,
      1.scan_type,
      s.name, 
      s.course, 
      s.year, 
      IFNULL(s.course, 'N/A') as course
  FROM scan_logs 1
  LEFT JOIN students s ON 1.student_id = s.student_id
  $whereClause
  ORDER BY 1.timestamp DESC
  LIMIT 100";

$stmt = $conn->prepare($sql); 

// Parameter bind if meron 
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

      $row['year'] = $year . $suffix . 'Year';
    
    }
    if (empty($row['course'])) {
      $row['course'] = 'N/A';

    }
    if (empty($row['timestamp'])) {
      $row['timestamp'] =date('M, d, Y h:i:s A' , strtotime($row['timestamp']));

    }

    $logs[] = $row;

  }

}

echo json_encode($logs); 
$stmt->close();
$conn->close();

// may error dito, need pa ifix later
?>

