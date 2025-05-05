<?php
session_start();
include('../db.php'); // Include database connection

// Super admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prevent deleting yourself (optional safety)
    if ($id == $_SESSION['id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header("Location: user_management.php");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Account deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete account.";
    }
}

header("Location: user_management.php");
exit();
?>
