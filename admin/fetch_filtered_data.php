<?php
include('../db.php');

if (isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $query = "SELECT * FROM student_registration WHERE id = $id";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Student not found.']);
    }
}
?>
