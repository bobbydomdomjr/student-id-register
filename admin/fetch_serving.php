<?php
session_start();
include('../db.php');

// Fetch the student currently being served (status = 'processing')
$serving = mysqli_query($conn, "SELECT firstname, lastname FROM student_registration WHERE status = 'processing' LIMIT 1");
$servingStudent = mysqli_fetch_assoc($serving);

// Output the serving student's name
if ($servingStudent) {
    echo $servingStudent['firstname'] . ' ' . $servingStudent['lastname'];
} else {
    echo '---';  // If no student is being served
}
?>
