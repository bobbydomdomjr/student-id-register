<?php
// admin/check_studentno.php

// 1) Include your DB connection.
//    Adjust the path if this file lives elsewhere.
global $conn;
require_once __DIR__ . '/../db.php';

// 2) Only proceed if studentno POSTed
if (!isset($_POST['studentno'])) {
    http_response_code(400);
    exit('bad request');
}

// 3) Ensure plain-text response
header('Content-Type: text/plain; charset=UTF-8');

$studentno = $_POST['studentno'];

// 4) Prepared statement to count matching studentno
if ($stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM student_registration WHERE studentno = ?")) {
    $stmt->bind_param("s", $studentno);
    $stmt->execute();
    $stmt->bind_result($cnt);
    $stmt->fetch();
    $stmt->close();

    echo ($cnt > 0) ? 'exists' : 'not exists';
    exit;
} else {
    // If prepare fails, signal error
    http_response_code(500);
    exit('error');
}
