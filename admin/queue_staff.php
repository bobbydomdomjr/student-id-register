<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user just logged in
$welcome_message = '';
if (isset($_SESSION['welcome_message'])) {
    $welcome_message = $_SESSION['welcome_message'];
    unset($_SESSION['welcome_message']);
}

// Fetch student statistics
$sql_course = "SELECT course, COUNT(*) AS count FROM student_registration GROUP BY course";
$result_course = $conn->query($sql_course) or die("Error in SQL (course): " . $conn->error);

$sql_monthly = "SELECT DATE_FORMAT(dob, '%Y-%m') AS month, COUNT(*) AS count FROM student_registration GROUP BY month ORDER BY month";
$result_monthly = $conn->query($sql_monthly) or die("Error in SQL (monthly): " . $conn->error);

$course_data = [];
$monthly_data = [];

while ($row = $result_course->fetch_assoc()) {
    $course_data[] = ['course' => $row['course'], 'count' => $row['count']];
}

while ($row = $result_monthly->fetch_assoc()) {
    $monthly_data[] = ['month' => $row['month'], 'count' => $row['count']];
}

// Fetch statistics
$total_today = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE DATE(registration_date) = '$today'");
$total_all = getCount($conn, "SELECT COUNT(*) FROM student_registration");
$total_pending = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'Pending'");
$total_processing = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'Processing'");
$total_done = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'Done'");



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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <div class="logo_name">Staff  Panel</div>
            <i class="bx bx-menu" id="btn"></i>
        </div>
        <ul class="nav-list">
            <li>
                <a href="staff_dashboard.php">
                    <i class="bx bx-home"></i>
                    <span class="links_name">Home</span>
                </a>
                <span class="tooltip">Home</span>
            </li>
            <li>
                <a href="#">
                    <i class="bx bx-user-plus"></i>
                    <span class="links_name">User Management</span>
                </a>
                <span class="tooltip">User Management</span>
            </li>
            <li>
                <a href="students.php">
                    <i class="bx bx-group"></i>
                    <span class="links_name">Student Management</span>
                </a>
                <span class="tooltip">Student Management</span>
            </li>
            <li>
                <a href="queue_staff.php">
                    <i class="bx bx-add-to-queue"></i>
                    <span class="links_name">Queue Management</span>
                </a>
                <span class="tooltip">Queue Management</span>
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

    <!-- Main Content -->
 <div class=home-section> 
        <div class="text">Home</div>
        <!-- Welcome Message Tooltip -->
        <?php if (!empty($welcome_message)) : ?>
            <div id="welcomeMessage">
                🎉 <?php echo htmlspecialchars($welcome_message); ?>
            </div>
        <?php endif; ?>
<hr>

<div class="container-fluid">
    <div class="row">
    <div class="d-flex justify-content-center gap-3 mt-4">
  <button id="btnCallNext" class="btn btn-success btn-lg">
    <i class="bi bi-megaphone-fill"></i> Call Next Student
  </button>

  <button id="btnRenotify" class="btn btn-warning btn-lg">
    <i class="bi bi-bell-fill"></i> Re-notify
  </button>

  <button id="btnSkip" class="btn btn-danger btn-lg">
    <i class="bi bi-forward-fill"></i> Skip
  </button>
</div>

        
        
  

        </div>
        <footer class="text-center text-muted mt-auto">
    <small>&copy; 2025 Student Registration System</small>
</footer>
        </div>

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
