<?php
// staff/api/fetch_by_status.php
session_start();
require_once('../../db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$status = $_GET['status'] ?? '';
$valid  = ['pending','processing','done','no-show'];
if (!in_array($status, $valid)) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
  SELECT studentno, firstname, lastname, registration_date
    FROM student_registration
   WHERE status = ?
   ORDER BY registration_date
");
$stmt->bind_param('s', $status);
$stmt->execute();
$result = $stmt->get_result();
$out = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($out);
