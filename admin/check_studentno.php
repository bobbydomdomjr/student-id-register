<?php
global $conn;
include './../db.php';

if (isset($_POST['studentno'])) {
    $studentno = $_POST['studentno'];
    // Use a prepared statement to prevent SQL injection (even though studentno is auto-generated)
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM student_registration WHERE studentno = ?");
    $stmt->bind_param("s", $studentno);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "exists";
    } else {
        echo "not exists";
    }
}