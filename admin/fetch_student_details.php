<?php
include('../db.php');
header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $id = (int) $_POST['id']; // Ensure ID is an integer for security
    $query = "SELECT * FROM student_registration WHERE id = $id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        echo json_encode(["error" => "Student not found."]);
    }
}
?>
