<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
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
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <!-- Boxicons CDN Link -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <!-- Bootstrap 4.3.1 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Font Awesome -->
  <!-- Font Awesome CSS for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
        }

        #sidebar {
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            color: white;
        }

        #sidebar a {
            padding: 15px;
            text-decoration: none;
            color: white;
            display: block;
        }

        #sidebar a:hover,
        #sidebar a.active {
            background-color: #495057;
        }

        #content {
            flex-grow: 1;
            padding: 20px;
        }

        .btn-sm i {
            margin: 0;
        }

        .modal-content {
            border-radius: 12px;
        }

        #pdf-content {
            display: none;
        }

        .navbar {
            background-color: #343a40;
        }

        #welcomeMessage {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 9999;
        }
        footer {
    flex-shrink: 0;
    font-size: 14px;
    color: #495057;
    letter-spacing: 0.5px;
    text-align: center;
    padding: 10px 0;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
        }

        .stat-card {
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        min-height: 140px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        font-size: 1.6rem;
        opacity: 0.85;
    }

    .stat-header {
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-details">
            <i class="bx bxl-angular icon"></i>
            <div class="logo_name">Admin Panel</div>
            <i class="bx bx-menu" id="btn"></i>
        </div>
        <ul class="nav-list">
            <li>
                <a href="dashboard.php">
                    <i class="bx bx-home"></i>
                    <span class="links_name">Home</span>
                </a>
                <span class="tooltip">Home</span>
            </li>
            <li>
                <a href="students.php">
                    <i class="bx bx-group"></i>
                    <span class="links_name">Student Management</span>
                </a>
                <span class="tooltip">Student Management</span>
            </li>
            <li>
                <a href="queue_manager.php">
                    <i class="bx bx-add-to-queue"></i>
                    <span class="links_name">Queue Management</span>
                </a>
                <span class="tooltip">Queue Management</span>
            </li>
            <li>
                <a href="#">
                    <i class="bx bx-file"></i>
                    <span class="links_name">Reports & Logs</span>
                </a>
                <span class="tooltip">Reports & Logs</span>
            </li>
            <li>
                <a href="logout.php" onclick="return confirmLogout()">
                    <i class="bx bx-log-out"></i>
                    <span class="links_name">Logout</span>
                </a>
                <span class="tooltip">Logout</span>
            </li>
        </ul>
    </div>

    <section class="home-section">
      <div class="text">Dashboard</div>
   
<hr>

<div class="container-fluid">
<script>
        // Function to play a notification sound when calling the next student
        function playNotificationSound() {
            const audio = new Audio('path_to_your_sound_file.mp3');
            audio.play();
        }
    </script>


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

        </div>
        <footer class="text-center text-muted mt-auto">
    <small>&copy; 2025 Student Registration System</small>
</footer>
        </div>
        </div>
   
    </section>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
     <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        let sidebar = document.querySelector(".sidebar");
        let closeBtn = document.querySelector("#btn");
        let searchBtn = document.querySelector(".bx-search");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();
        });

        searchBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();
        });

        function menuBtnChange() {
            if (sidebar.classList.contains("open")) {
                closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        }

        // Show welcome message automatically
        document.addEventListener("DOMContentLoaded", function () {
            const welcomeMessage = document.getElementById('welcomeMessage');
            if (welcomeMessage) {
                welcomeMessage.style.display = 'block';
                setTimeout(() => {
                    welcomeMessage.style.display = 'none';
                }, 3000); // Hide after 3 seconds
            }
        });

        // Logout Confirmation
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }
    </script>

</body>
</html>

