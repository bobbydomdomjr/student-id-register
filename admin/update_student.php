<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
include('../db.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $studentno = $_POST['studentno'];
    $firstname = $_POST['firstname'];
    $middleinitial = $_POST['middleinitial'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $contactname = $_POST['contactname'];
    $contactno = $_POST['contactno'];
    $relationship = $_POST['relationship'];

    // Update student details in the database
    $sql = "UPDATE student_registration 
            SET studentno = ?, firstname = ?, middleinitial = ?, lastname = ?, email = ?, contactname = ?, contactno = ?, relationship = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssi', $studentno, $firstname, $middleinitial, $lastname, $email, $contactname, $contactno, $relationship, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating student!";
    }

    // Redirect back to the students list page
    header("Location: students.php");
    exit();
}
?>
