<?php
session_start();
require '../db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stmt = $conn->prepare("SELECT * FROM messages ORDER BY created_at ASC");
$stmt->execute();
$result = $stmt->get_result();

$messages_html = '';
$unread = 0;
$unread_ids = [];

while ($row = $result->fetch_assoc()) {
    $is_sender = ($row['sender_id'] == $user_id && $row['sender_role'] == $role);

    // Get sender name
    $sender_name = 'Unknown';
    if ($row['sender_role'] === 'admin') {
        $stmtSender = $conn->prepare("SELECT name FROM admin WHERE id = ?");
    } elseif ($row['sender_role'] === 'staff') {
        $stmtSender = $conn->prepare("SELECT name FROM staff WHERE id = ?");
    }
    if (isset($stmtSender)) {
        $stmtSender->bind_param("i", $row['sender_id']);
        $stmtSender->execute();
        $senderResult = $stmtSender->get_result();
        if ($sender = $senderResult->fetch_assoc()) {
            $sender_name = $sender['name'];
        }
    }

    // Format timestamp
    $timestamp = date('Y-m-d H:i', strtotime($row['created_at']));

    // Build message HTML block with sender and timestamp
    $messages_html .= '<div class="mb-1 ' . ($is_sender ? 'text-end' : 'text-start') . '">';
    $messages_html .= '<small class="text-muted d-block">' . htmlspecialchars($sender_name) . ' (' . ucfirst($row['sender_role']) . ') - ' . $timestamp . '</small>';
    $messages_html .= '<span class="badge bg-' . ($is_sender ? 'primary' : 'secondary') . '">' . htmlspecialchars($row['message']) . '</span>';
    $messages_html .= '</div>';

    // Collect unread info if message is unread and addressed to this user (or broadcast)
    if (
        ($row['receiver_id'] == $user_id || $row['receiver_id'] == 0) && // receiver is user or broadcast
        $row['receiver_role'] === $role &&
        $row['is_read'] == 0
    ) {
        $unread++;
        $unread_ids[] = $row['id'];
    }
}

// Mark unread messages as read once fetched (optional, so they won't keep showing as unread)
if (!empty($unread_ids)) {
    $ids_placeholder = implode(',', array_fill(0, count($unread_ids), '?'));
    $types = str_repeat('i', count($unread_ids));
    $stmtUpdate = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id IN ($ids_placeholder)");
    $stmtUpdate->bind_param($types, ...$unread_ids);
    $stmtUpdate->execute();
}

echo json_encode([
    'messages' => $messages_html,
    'unread' => $unread
]);
