<?php
// staff/fetch_dashboard.php
session_start();
require_once('../db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
  http_response_code(403);
  exit;
}

// 1) Counts
$statuses = ['pending','processing','done'];
$counts = [];
foreach ($statuses as $st) {
  $stmt = $conn->prepare("SELECT COUNT(*) FROM student_registration WHERE status = ?");
  $stmt->bind_param('s', $st);
  $stmt->execute();
  $stmt->bind_result($c);
  $stmt->fetch();
  $counts[$st] = $c;
  $stmt->close();
}

// 2) Now Serving
$stmt = $conn->prepare("
  SELECT studentno, firstname, lastname
    FROM student_registration
   WHERE now_serving = 1
   LIMIT 1
");
$stmt->execute();
$serv = $stmt->get_result()->fetch_assoc() ?: null;
$stmt->close();

// 3) Next: processing then pending
$stmt = $conn->prepare("
  SELECT studentno, firstname, lastname
    FROM student_registration
   WHERE status='processing' AND now_serving=0
   ORDER BY updated_at ASC
   LIMIT 1
");
$stmt->execute();
$next = $stmt->get_result()->fetch_assoc() ?: null;
$stmt->close();

if (!$next) {
  $stmt = $conn->prepare("
    SELECT studentno, firstname, lastname
      FROM student_registration
     WHERE status='pending'
     ORDER BY registration_date ASC
     LIMIT 1
  ");
  $stmt->execute();
  $next = $stmt->get_result()->fetch_assoc() ?: null;
  $stmt->close();
}

// Return everything
echo json_encode([
  'counts'  => $counts,
  'serving' => $serv,
  'next'    => $next
]);
