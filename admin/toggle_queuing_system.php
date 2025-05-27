<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

include('../db.php');

if (isset($_POST['toggle_queuing_system'])) {
    // Fetch the current status of the queuing system from the 'settings' table
    $result = $conn->query("SELECT * FROM settings WHERE id = 1"); // Assuming there's only one row for the configuration
    $config = $result->fetch_assoc();
    $newStatus = ($config['queuing_enabled'] == 1) ? 0 : 1; // If enabled, disable; if disabled, enable
    
    // Toggle the queuing system status
    if ($config['queuing_enabled'] == 1) {
        // Disable the queuing system
        $conn->query("UPDATE settings SET queuing_enabled = 0 WHERE id = 1");
        $_SESSION['success_message'] = 'Queuing System Disabled.';
    } else {
        // Enable the queuing system
        $conn->query("UPDATE settings SET queuing_enabled = 1 WHERE id = 1");
        $_SESSION['success_message'] = 'Queuing System Enabled.';
    }
    
    header("Location: queue_manager.php"); // Redirect back to the queue manager
    exit();
}
?>
