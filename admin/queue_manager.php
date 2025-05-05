<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Pagination setup
$limit = 10; // number of results per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Query to fetch student data with search
$sql_students = "SELECT * FROM student_registration WHERE CONCAT(firstname, ' ', lastname, ' ', course) LIKE '%$search_term%' LIMIT $limit OFFSET $offset";
$result_students = $conn->query($sql_students) or die("Error in SQL (students): " . $conn->error);

// Count total students for pagination
$sql_count = "SELECT COUNT(*) AS total FROM student_registration WHERE CONCAT(firstname, ' ', lastname, ' ', course) LIKE '%$search_term%'";
$result_count = $conn->query($sql_count);
$total_students = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_students / $limit);

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
// Check if there are any success or error messages in the session
$showModal = false;
$message = '';
$type = ''; // 'success' or 'error'

if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $type = 'success';
    unset($_SESSION['success_message']);
    $showModal = true;
} elseif (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $type = 'error';
    unset($_SESSION['error_message']);
    $showModal = true;
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
        footer {
            margin-top: 100px;
            font-size: 14px;
            color: #495057;
            letter-spacing: 0.5px;
        }
        .status-dropdown {
    padding: 2px 4px;
    font-size: 0.9rem;
        }
        .custom-toast {
                    top: 20px;
                    right: 20px;
                    background: #fff;
                    color: #333;
                    padding: 14px 20px;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
                    z-index: 9999;
                    font-family: "Segoe UI", Roboto, sans-serif;
                    max-width: 280px;
                    width: auto;
                    font-size: 14px;
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    border-left: 5px solid #dc3545;
                    animation: slideDown 0.3s ease-out;
    }

    .custom-toast.success {
        border-color: #28a745;
    }

    .custom-toast.error {
        border-color: #dc3545;
    }

    .custom-toast strong {
        display: block;
        font-size: 14px;
        margin-bottom: 3px;
    }

    .custom-toast .close {
        float: right;
        font-size: 18px;
        color: #999;
        border: none;
        background: none;
        cursor: pointer;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }.custom-toggle-switch {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.toggle-label {
    font-weight: 500;
    font-size: 16px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    background-color: #ccc;
    border-radius: 34px;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: .4s;
}

.slider:before {
    content: "";
    position: absolute;
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    border-radius: 50%;
    transition: .4s;
}

input:checked + .slider {
    background-color: #28a745;
}

input:checked + .slider:before {
    transform: translateX(24px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
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

    <!-- Main Content -->
 <div class=home-section> 
        <div class="text">View Real-time Queue Status</div>
        <!-- Welcome Message Tooltip -->
<hr>

<div class="container-fluid">
            <!-- Student Table -->
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>Student No.</th>
                        <th>Full Name</th>
                        <th>Course/Year/Block</th>
                        <th>Email Address</th>
                        <th>Registered Date & Time</th>
                        <th>Status</th>
                        <th style="white-space: nowrap;">Actions</th>

                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $result_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $student['studentno']; ?></td>
                            <td>
                            <?php 
                                echo htmlspecialchars($student['firstname'] ?? '') . ' ' . 
                                    htmlspecialchars($student['middleinitial'] ?? '') . ' ' . 
                                    htmlspecialchars($student['lastname'] ?? '') . ' ' . 
                                    htmlspecialchars($student['extname'] ?? '');
                            ?>
                            </td>
                            <td><?php echo htmlspecialchars($student['course']). ' ' . htmlspecialchars($student['yearlevel']) . ' ' . htmlspecialchars($student['block']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['registration_date']); ?></td>
                            <td>
    <?php 
        $status = trim(htmlspecialchars($student['status']));
        $status = ucfirst(strtolower($status));
        
        $statusColors = [
            'Waiting' => 'bg-warning',
            'Processing' => 'bg-info',
            'Completed' => 'bg-success'
        ];
        $badgeClass = $statusColors[$status] ?? 'bg-secondary';
    ?>
    <span class="badge <?php echo $badgeClass; ?> status-badge" data-id="<?php echo $student['id']; ?>"><?php echo $status; ?></span>
</td>

<td>
    <select class="form-control form-control-sm status-dropdown" data-id="<?php echo $student['id']; ?>">
        <option value="Waiting" <?php echo $status == 'Waiting' ? 'selected' : ''; ?>>Waiting</option>
        <option value="Processing" <?php echo $status == 'Processing' ? 'selected' : ''; ?>>Processing</option>
        <option value="Completed" <?php echo $status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
    </select>
</td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
<!-- Modal Wrapper (outside .table-responsive or container divs) -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="z-index: 1050;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Student Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalContent">Loading...</div>
    </div>
  </div>
</div>
<!-- Button to Enable/Disable Queuing System -->

<?php 
$result = $conn->query("SELECT * FROM settings WHERE id = 1");
$config = $result->fetch_assoc();
$queuingEnabled = $config['queuing_enabled'] == 1;
?>

<form method="POST" action="toggle_queuing_system.php" id="queuingSystemForm">
    <div class="custom-toggle-switch">
        <label class="switch">
            <input type="checkbox" name="toggle_queuing_system" onchange="document.getElementById('queuingSystemForm').submit();" <?php echo $queuingEnabled ? 'checked' : ''; ?>>
            <span class="slider round"></span>
        </label>
        <span class="toggle-label"><?php echo $queuingEnabled ? 'Queuing Enabled' : 'Queuing Disabled'; ?></span>
    </div>
</form>



            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_term); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_term); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<!-- Toast Notification -->
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 1050;">
    <div id="statusToast" class="toast" role="alert" data-delay="3000" style="min-width: 250px;">
        <div class="toast-header">
            <strong class="mr-auto" id="toastTitle">Status</strong>
            <small>Now</small>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body" id="toastBody">
            Status updated successfully!
        </div>
    </div>
</div>

<!-- Toast Container -->
<!-- Toast Container -->
<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 1055;"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let sidebar = document.querySelector(".sidebar");
    let closeBtn = document.querySelector("#btn");

    closeBtn.addEventListener("click", () => {
        sidebar.classList.toggle("open");
    });

    // Logout Confirmation
    function confirmLogout() {
        return confirm('Are you sure you want to logout?');
    }

    function showToast(title, message, isSuccess = true) {
        $('#toastTitle').text(title);
        $('#toastBody').text(message);

        const toast = $('#statusToast');
        toast.removeClass('bg-success bg-danger text-white');

        if (isSuccess) {
            toast.addClass('bg-success text-white');
        } else {
            toast.addClass('bg-danger text-white');
        }

        toast.toast('show');
    }

    $(document).on('change', '.status-dropdown', function () {
        let newStatus = $(this).val();
        let studentId = $(this).data('id');

        $.ajax({
            url: 'update_status.php',
            method: 'POST',
            data: { id: studentId, status: newStatus },
            success: function (response) {
                try {
                    let res = JSON.parse(response);
                    if (res.success) {
// Map status to corresponding icons
let statusIcons = {
    "Waiting": "⏳",
    "Processing": "🔄",
    "Completed": "✅"
};

// Get the appropriate icon for the new status
let icon = statusIcons[newStatus] || "ℹ️";

// Show the toast with the icon and status message
showToast(icon, 'Status updated to ' + newStatus);

// Update badge text and color
let badge = $('.status-badge[data-id="' + studentId + '"]');
badge.text(newStatus);

// Map status to corresponding badge color
let badgeClass = {
    'Waiting': 'bg-warning',
    'Processing': 'bg-info',
    'Completed': 'bg-success'
}[newStatus] || 'bg-secondary';  // Default to secondary if no match

// Update badge class
badge.removeClass().addClass('badge status-badge ' + badgeClass);

                    } else {
                        showToast('Error', 'Update failed: ' + res.message, false);
                    }
                } catch (e) {
                    showToast('Error', 'Unexpected error while updating.', false);
                }
            },
            error: function () {
                showToast('Error', 'Failed to update status.', false);
            }
        });
    });

    <?php if ($showModal): ?>
    $(document).ready(function () {
        $('#messageModal').modal('show');
    });
    <?php endif; ?>


    function showToast(title, message, isSuccess = true) {
        const toastId = 'toast_' + Date.now();

        const toast = `
            <div id="${toastId}" class="custom-toast ${isSuccess ? 'success' : 'error'}">
                <strong>${title}</strong>
                <div>${message}</div>
            </div>
        `;

        $('#toast-container').append(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            $('#' + toastId).fadeOut(300, function () { $(this).remove(); });
        }, 3000);
    }
$(document).on('click', '.toggle-status-btn', function () {
    let studentId = $(this).data('id');
    let action = $(this).data('action'); // 'enable' or 'disable'

    $.ajax({
        url: 'toggle_status.php',
        method: 'POST',
        data: { id: studentId, action: action },
        success: function (response) {
            try {
                let res = JSON.parse(response);
                if (res.success) {
                    showToast('Success', 'Student has been ' + action + 'd successfully.');
                    location.reload(); // Or update button text/status dynamically
                } else {
                    showToast('Error', 'Operation failed: ' + res.message, false);
                }
            } catch (e) {
                showToast('Error', 'Unexpected error occurred.', false);
            }
        },
        error: function () {
            showToast('Error', 'Request failed.', false);
        }
    });
});

</script>



</body>

</html>
