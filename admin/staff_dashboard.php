<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../admin/index.php");
    exit();
}

include('../db.php');

// Fetch students in the queue (pending status)
$queue = mysqli_query($conn, "SELECT id, firstname FROM student_registration WHERE status = 'pending' LIMIT 10");

// Fetch the current "processing" student
$serving = mysqli_query($conn, "SELECT firstname FROM student_registration WHERE status = 'processing' LIMIT 1");
$servingStudent = mysqli_fetch_assoc($serving);

// Call next student in the queue
if (isset($_POST['call_next'])) {
    $nextStudentId = $_POST['next_student_id'];
    // Mark next student as "processing"
    $query = "UPDATE student_registration SET status = 'processing' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $nextStudentId);
    $stmt->execute();
}

// Mark student status (pending, processing, done)
if (isset($_POST['mark_status'])) {
    $studentId = $_POST['student_id'];
    $newStatus = $_POST['new_status'];

    $query = "UPDATE student_registration SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newStatus, $studentId);
    $stmt->execute();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Queue Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Function to play a notification sound when calling the next student
        function playNotificationSound() {
            const audio = new Audio('path_to_your_sound_file.mp3');
            audio.play();
        }
    </script>
</head>
<body class="p-4">

<div class="container">
    <h1>Queue Dashboard</h1>

    <!-- Current Serving Student -->
    <div class="my-4">
        <h3>Currently Serving: <?= $servingStudent['firstname'] ?? 'None' ?></h3>
    </div>

    <!-- Queue -->
    <div class="my-4">
        <h3>In Queue</h3>
        <ul class="list-group">
            <?php while ($student = mysqli_fetch_assoc($queue)) : ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $student['firstname'] ?>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="next_student_id" value="<?= $student['id'] ?>">
                        <button type="submit" name="call_next" class="btn btn-success" onclick="playNotificationSound()">Call Next</button>
                    </form>

                    <form method="POST" action="" class="ms-2">
                        <select name="new_status" class="form-select" onchange="this.form.submit()">
                            <option value="pending" <?= ($student['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= ($student['status'] == 'processing') ? 'selected' : '' ?>>Processing</option>
                            <option value="done" <?= ($student['status'] == 'done') ? 'selected' : '' ?>>Done</option>
                        </select>
                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
