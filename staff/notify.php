<?php
// staff/notify.php
session_start();
require_once('../db.php');
header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    http_response_code(403);
    exit;
}

$no = $_POST['studentno'] ?? '';
if ($no) {
    $stmt = $conn->prepare("
      UPDATE student_registration
         SET notified = 1
       WHERE studentno = ?
    ");
    $stmt->bind_param('s', $no);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['ok' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing studentno']);
}
