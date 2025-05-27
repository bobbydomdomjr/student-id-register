<?php
// includes/init.php
if (defined('APP_INIT')) {
    return;
}
const APP_INIT = true;

// Start session & auth check
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: index.html');
    exit();
}

// Database connection
require_once __DIR__ . '/../db.php';

// Development error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Helper to fetch single-value counts
function getCount($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_row()) {
        return (int)$row[0];
    }
    return 0;
}
