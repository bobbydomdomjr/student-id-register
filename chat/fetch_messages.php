<?php
// chat/fetch_messages.php
session_start();
require_once __DIR__ . '/../db.php';

// If user not logged in → return empty
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    echo json_encode([
        'messages' => '',
        'unread'   => 0
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// 1) Fetch only messages where this role appears as sender_role OR receiver_role
$stmt = $conn->prepare("
    SELECT *
    FROM messages
    WHERE sender_role = ? OR receiver_role = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("ss", $role, $role);
$stmt->execute();
$result = $stmt->get_result();

$messages_html = '';
$unread       = 0;
$unread_ids   = [];

while ($row = $result->fetch_assoc()) {
    $is_sender = ($row['sender_id'] == $user_id && $row['sender_role'] === $role);

    // Lookup sender's username (from `admin` table)
    $sender_name = 'Unknown';
    if ($row['sender_role'] === 'admin' || $row['sender_role'] === 'staff') {
        $stm = $conn->prepare("SELECT username FROM admin WHERE id = ?");
        $stm->bind_param("i", $row['sender_id']);
        $stm->execute();
        $res = $stm->get_result();
        if ($r2 = $res->fetch_assoc()) {
            $sender_name = $r2['username'];
        }
        $stm->close();
    }

    // Format timestamp
    $timestamp = date('Y-m-d H:i', strtotime($row['created_at']));

    // Build each line with an avatar + bubble.
    // If receiver (incoming): avatar on left, bubble next.
    // If sender (outgoing): bubble first, avatar on right.
    if ($is_sender) {
        $messages_html .= '
          <div class="d-flex justify-content-end align-items-start mb-2">
            <div class="chat-bubble chat-bubble-sender">
              <div class="small text-white-50 mb-1">' . htmlspecialchars($sender_name) . ' (' . ucfirst($row['sender_role']) . ')</div>
              <div>' . nl2br(htmlspecialchars($row['message'])) . '</div>
              <div class="small text-white-50 text-end mt-1">' . $timestamp . '</div>
            </div>
            <div class="avatar avatar-sender ms-2">
              <i class="fas fa-user"></i>
            </div>
          </div>
        ';
    } else {
        $messages_html .= '
          <div class="d-flex justify-content-start align-items-start mb-2">
            <div class="avatar avatar-receiver me-2">
              <i class="fas fa-user"></i>
            </div>
            <div class="chat-bubble chat-bubble-receiver">
              <div class="small text-muted mb-1">' . htmlspecialchars($sender_name) . ' (' . ucfirst($row['sender_role']) . ')</div>
              <div>' . nl2br(htmlspecialchars($row['message'])) . '</div>
              <div class="small text-muted text-end mt-1">' . $timestamp . '</div>
            </div>
          </div>
        ';
    }

    // Count unread only if addressed to me (receiver_role matches AND receiver_id = 0 or my ID)
    if (
        $row['receiver_role'] === $role &&
        ($row['receiver_id'] == 0 || $row['receiver_id'] == $user_id) &&
        $row['is_read'] == 0
    ) {
        $unread++;
        $unread_ids[] = $row['id'];
    }
}
$stmt->close();

// 2) Mark unread messages as read (only those addressed to me)
if (!empty($unread_ids)) {
    $placeholders = implode(',', array_fill(0, count($unread_ids), '?'));
    $types        = str_repeat('i', count($unread_ids));
    $stmtUpd      = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id IN ($placeholders)");
    $stmtUpd->bind_param($types, ...$unread_ids);
    $stmtUpd->execute();
    $stmtUpd->close();
}

// 3) Return JSON
echo json_encode([
    'messages' => $messages_html,
    'unread'   => $unread
]);
