<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toggle the value based on the current setting
    $result = $conn->query("SELECT queuing_enabled FROM settings WHERE id = 1");
    $current = $result->fetch_assoc()['queuing_enabled'];
    $newValue = $current == 1 ? 0 : 1;

    $conn->query("UPDATE settings SET queuing_enabled = $newValue WHERE id = 1");
    $_SESSION['success_message'] = $newValue ? "Queuing system enabled." : "Queuing system disabled.";
}
    
    header("Location: queue_manager.php"); // Redirect back to the queue manager
    exit();
?>
