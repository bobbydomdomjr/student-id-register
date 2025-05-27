<?php
session_start();
include('../db.php'); // Include database connection

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id']; // Cast the ID to an integer for safety

    // Check if the ID is valid
    if ($id > 0) {
        // Prepare the DELETE query
        $query = "DELETE FROM student_registration WHERE id = $id";

        if ($conn->query($query) === TRUE) {
            $_SESSION['success_message'] = "Student deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting student: " . $conn->error;
        }
    } else {
        $_SESSION['error_message'] = "Invalid student ID.";
    }
} else {
    $_SESSION['error_message'] = "Student ID not provided.";
}

header("Location: students.php"); // Redirect back to the students page
exit();
?>
