<?php
session_start();
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$message = trim($data['message']);
$sender_id = $_SESSION['user_id'];
$sender_role = $_SESSION['role'];

// Determine receiver role
$receiver_role = ($sender_role === 'admin') ? 'staff' : 'admin';

// Optional: Determine receiver_id, or set it to 0 for broadcast-like

$stmt = $conn->prepare("INSERT INTO messages (sender_id, sender_role, receiver_role, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
$stmt->bind_param("isss", $sender_id, $sender_role, $receiver_role, $message);
$stmt->execute();

echo json_encode(['success' => true]);
?>
