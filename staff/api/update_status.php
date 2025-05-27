<?php
// staff/api/update_status.php
session_start();
require_once('../../db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    http_response_code(403);
    echo json_encode(['ok'=>false]);
    exit;
}

$studentno = $_POST['studentno'] ?? '';
$status    = $_POST['status']    ?? '';
$valid     = ['pending','processing','done','no-show'];

if ($studentno && in_array($status, $valid)) {
    // If moving into processing → make them now_serving; otherwise clear now_serving
    if ($status === 'processing') {
        $conn->query("UPDATE student_registration SET now_serving=0 WHERE now_serving=1");
        $conn->query("UPDATE student_registration SET now_serving=1 WHERE studentno='". $conn->real_escape_string($studentno) ."'");
      } else {
        // e.g. done, no-show, pending, etc.
        $conn->query("UPDATE student_registration SET now_serving=0 WHERE studentno='". $conn->real_escape_string($studentno) ."'");
      }
  
      $stmt = $conn->prepare("
        UPDATE student_registration
           SET status = ?, updated_at = NOW()
         WHERE studentno = ?
      ");
    $stmt->bind_param('ss', $status, $studentno);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok'=>true]);
} else {
    http_response_code(400);
    echo json_encode(['ok'=>false]);
}
