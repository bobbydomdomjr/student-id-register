<?php
function log_action($conn, $user_id, $username, $role, $action) {
    if (!$conn) {
        error_log("log_action failed: no DB connection");
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, username, role, action, log_time)
                            VALUES (?, ?, ?, ?, NOW())");

    if (!$stmt) {
        error_log("log_action prepare failed: " . $conn->error);
        return false;
    }

    if (!$stmt->bind_param("isss", $user_id, $username, $role, $action)) {
        error_log("log_action bind_param failed: " . $stmt->error);
        return false;
    }

    if (!$stmt->execute()) {
        error_log("log_action execute failed: " . $stmt->error);
        return false;
    }

    $stmt->close();
    return true;
}
?>
