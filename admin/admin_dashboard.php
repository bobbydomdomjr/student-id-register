<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Sample welcome message assignment for testing
$welcome_message = "Welcome to the system!"; // You can set this based on user data or logic

// Or set dynamically, for example:
$welcome_message = isset($_SESSION['username']) ? "Welcome, " . $_SESSION['username'] : "Welcome, Guest!";




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
$total_pending = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'waiting'");
$total_processing = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'processing'");
$total_done = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status = 'Completed'");



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

/* Container to take at least the full viewport height */
.container-fluid {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Ensure footer stays at the bottom */
footer {
    background-color: #f8f9fa;
    padding: 10px;
    position: relative;
    width: 100%;
    bottom: 0;
}/* Toast Notification Styles */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 16px;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.5s, visibility 0.5s ease-in-out;
}

/* Toast visible after it is triggered */
.toast.show {
    opacity: 1;
    visibility: visible;
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
                <a href="admin_dashboard.php">
                    <i class="bx bx-home"></i>
                    <span class="links_name">Home</span>
                </a>
                <span class="tooltip">Home</span>
            </li>
            <li>
                <a href="user_management.php">
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
                <a href="queue_manager.php">
                    <i class="bx bx-add-to-queue"></i>
                    <span class="links_name">Queue Management</span>
                </a>
                <span class="tooltip">Queue Management</span>
            </li>
            <li>
                <a href="ad_manager.php">
                    <i class="bx bx-play-circle"></i>
                    <span class="links_name">Ads Management</span>
                </a>
                <span class="tooltip">Ads Management</span>
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
            <!-- Toast Notification -->
    <?php if (!empty($welcome_message)) : ?>
        <div id="toast" class="toast">
            🎉 <?php echo htmlspecialchars($welcome_message); ?>
        </div>
    <?php endif; ?>
      <div class="text">Dashboard</div>
<hr>

<div class="container-fluid d-flex flex-column min-vh-100">
<div class="row flex-grow-1">
    <div class="col-md-auto col-sm-auto">
            <div class="card text-white bg-primary stat-card">
                <div class="card-body">
                    <div class="stat-header">
                        <i class="fas fa-calendar-day stat-icon"></i>
                        Students Today
                    </div>
                    <div class="stat-number mt-3">
                        <?php echo $total_today; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="col-md-auto col-sm-auto">
            <div class="card text-white bg-secondary stat-card">
                <div class="card-body">
                    <div class="stat-header">
                        <i class="fas fa-users stat-icon"></i>
                        Total Students
                    </div>
                    <div class="stat-number mt-3">
                        <?php echo $total_all; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Students -->
        <div class="col-md-auto col-sm-auto">
            <div class="card text-dark bg-warning stat-card">
                <div class="card-body">
                    <div class="stat-header">
                        <i class="fas fa-user-clock stat-icon"></i>
                        In Queue
                    </div>
                    <div class="stat-number mt-3">
                        <?php echo $total_pending; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Processing Students -->
        <div class="col-md-auto col-sm-auto">
            <div class="card text-white bg-success stat-card">
                <div class="card-body">
                    <div class="stat-header">
                        <i class="fas fa-spinner stat-icon"></i>
                        In Progress
                    </div>
                    <div class="stat-number mt-3">
                        <?php echo $total_processing; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Students -->
        <div class="col-md-auto col-sm-auto">
            <div class="card text-white bg-info stat-card">
                <div class="card-body">
                    <div class="stat-header">
                        <i class="fas fa-check-circle stat-icon"></i>
                        Completed
                    </div>
                    <div class="stat-number mt-3">
                        <?php echo $total_done; ?>
                    </div>
                </div>
            </div>
        </div>

        

        <footer class="text-center text-muted mt-auto">
        <small>&copy; 2025 Student Registration System</small>
    </footer>
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

// Ensure that the toast appears when the page loads
document.addEventListener('DOMContentLoaded', function () {
    var toast = document.getElementById('toast');
    if (toast) {
        // Add the 'show' class to display the toast
        toast.classList.add('show');
        
        // Hide the toast after 4 seconds
        setTimeout(function () {
            toast.classList.remove('show');
        }, 4000); // 4000ms = 4 seconds
    }
});


    </script>

</body>
</html>
