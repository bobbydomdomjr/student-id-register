<?php
session_start();
include('../db.php');

// Fetch all students currently in the queue
$queue = mysqli_query($conn, "SELECT firstname, lastname FROM student_registration WHERE status = 'pending'");

if (mysqli_num_rows($queue) > 0) {
    while ($student = mysqli_fetch_assoc($queue)) {
        echo "<li class='list-group-item list-group-item-light fw-semibold'>" 
            . $student['firstname'] . " " . $student['lastname'] 
            . "</li>";
    }
} else {
    echo "<li class='list-group-item text-muted'>No students in the queue.</li>";
}
?>
