<?php
include('../db.php');

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Update status to 'processing'
    $stmt = $conn->prepare("UPDATE student_registration SET status = 'processing' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'failed']);
    }
}
?>
