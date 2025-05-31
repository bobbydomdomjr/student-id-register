<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing student ID']);
  exit;
}

$id = (int) $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM student_registration WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($student = $result->fetch_assoc()) {
  echo json_encode($student);
} else {
  http_response_code(404);
  echo json_encode(['error' => 'Student not found']);
}
