<?php
// staff/queue_action.php
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
    // Clear previous now_serving
    $conn->query("UPDATE student_registration SET now_serving=0 WHERE now_serving=1");

    // Set this student to processing & now_serving
    $stmt = $conn->prepare("
        UPDATE student_registration
           SET status='processing',
               now_serving=1,
               updated_at=NOW()
         WHERE studentno=?
    ");
    $stmt->bind_param('s', $no);
    $stmt->execute();
    $stmt->close();

    // Reset notified flag
    $upd = $conn->prepare("
        UPDATE student_registration
           SET notified=0
         WHERE studentno=?
    ");
    $upd->bind_param('s', $no);
    $upd->execute();
    $upd->close();

    echo json_encode(['ok'=>true]);
} else {
    http_response_code(400);
    echo json_encode(['ok'=>false]);
}
