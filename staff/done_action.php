<?php
// staff/done_action.php
session_start();
require_once('../db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
  http_response_code(403);
  echo json_encode(['ok'=>false]);
  exit;
}

$no = $_POST['studentno'] ?? '';
if ($no) {
  // 1) mark student done, clear now_serving
  $stmt = $conn->prepare("
    UPDATE student_registration
       SET status='done',
           now_serving=0,
           updated_at=NOW()
     WHERE studentno=?
  ");
  $stmt->bind_param('s',$no);
  $stmt->execute();
  $stmt->close();
  echo json_encode(['ok'=>true]);
} else {
  http_response_code(400);
  echo json_encode(['ok'=>false]);
}
