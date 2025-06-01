<?php
// chat/send_message.php
session_start();
require_once __DIR__ . '/../db.php';

// If user isn’t logged in, abort
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$message = trim($data['message']);
$sender_id   = $_SESSION['user_id'];
$sender_role = $_SESSION['role'];

// Determine receiver_role (always “other” role)
$receiver_role = ($sender_role === 'admin') ? 'staff' : 'admin';

// For now, we broadcast to all in the opposite role (receiver_id = 0).
// If you want 1:1, you can pass a receiver_id in $data, but for simplicity:
$receiver_id = 0;

$stmt = $conn->prepare("
    INSERT INTO messages 
        (sender_id, sender_role, receiver_id, receiver_role, message, is_read, created_at)
    VALUES (?, ?, ?, ?, ?, 0, NOW())
");
$stmt->bind_param("issss", $sender_id, $sender_role, $receiver_id, $receiver_role, $message);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
