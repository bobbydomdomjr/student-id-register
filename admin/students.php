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
    <title>All Students</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
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
        .navbar {
            background-color: #343a40;
        }
        .table th, .table td {
        text-align: center;
        }
        .search-bar {
            width: 300px;
            float: right;
            margin-bottom: 20px;
        }
        table {
  width: 100%;
  table-layout: auto;
        }
        .success-message {
    background-color: #28a745;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}

.error-message {
    background-color: #dc3545;
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
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
    <div class="home-section">
        <div class="text">Registered Students</div>
        <hr>

        <div class="container-fluid">
        
        <form method="GET" class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <div class="btn-group mb-3">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="export_csv.php"><i class="fas fa-file-csv me-2"></i>CSV</a>
                <a class="dropdown-item" href="export_pdf.php"><i class="fas fa-file-pdf me-2"></i>PDF</a>
            </div>
        </div>

        <!-- Import Button -->
        <form action="import_students.php" method="POST" enctype="multipart/form-data" class="d-inline-block ml-2">
    <label class="btn btn-primary btn-sm mb-1">
        <i class="fas fa-file-import"></i> Import
        <input type="file" name="csv_file" accept=".csv" hidden onchange="this.form.submit()">
    </label>
</form>
    </div>

    <!-- Search Input -->
    <input type="text" id="searchInput" name="search" class="form-control search-bar" placeholder="Search for students..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" />
</form>

            <!-- Student Table -->
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>Student No.</th>
                        <th>Full Name</th>
                        <th>Contact Name</th>
                        <th>Contact Number</th>
                        <th>Registered Date & Time</th>
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
                            <td><?php echo htmlspecialchars($student['contactname']); ?></td>
                            <td><?php echo htmlspecialchars($student['contactno']); ?></td>
                            <td><?php echo htmlspecialchars($student['registration_date']); ?></td>
                            

 <td>
        <!-- Modify the "View" button in your student table -->
        <button class="btn btn-info btn-sm view-btn" data-id="<?php echo $student['id']; ?>" title="View">
            <i class="fas fa-eye"></i>
        </button>

        <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
            <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this student?')">
            <i class="fas fa-trash"></i>
        </a>

</td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
            <script>
$(document).ready(function () {
    // Real-time search functionality
    $('#searchInput').on('input', function () {
        var searchTerm = $(this).val(); // Get the search term

        if (searchTerm.length >= 2) {  // Only search if at least 2 characters are entered
            // Perform the AJAX request for real-time search
            $.ajax({
                url: 'search_students.php', // PHP file that processes the search query
                method: 'GET',
                data: { search: searchTerm }, // Send the search term to the server
                success: function (response) {
                    // Clear the table and append new rows
                    $('table tbody').empty();
                    var data = JSON.parse(response); // Assuming the response is in JSON format

                    // Loop through the data and append rows to the table
                    data.students.forEach(function (student) {
                        var row = `
                            <tr>
                                <td>${student.studentno}</td>
                                <td>${student.firstname} ${student.middleinitial} ${student.lastname} ${student.extname}</td>
                                <td>${student.contactname}</td>
                                <td>${student.contactno}</td>
                                <td>${student.registration_date}</td>
                                <td>
                                    <button class="btn btn-info btn-sm view-btn" data-id="${student.id}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm notify-btn" data-id="${student.id}" title="Notify">
                                        <i class="fas fa-bell"></i>
                                    </button>
                                    <a href="edit_student.php?id=${student.id}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_student.php?id=${student.id}" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this student?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                        $('table tbody').append(row);
                    });
                },
                error: function () {
                    alert('Error while searching.');
                }
            });
        } else {
            // If the search term is less than 2 characters, clear the table
            $('table tbody').empty();
        }
    });
});

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


        let sidebar = document.querySelector(".sidebar");
        let closeBtn = document.querySelector("#btn");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
        });

        // Logout Confirmation
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }


        $(document).ready(function () {
    $('#filter').click(function () {
        let fromDate = $('#date_from').val();
        let toDate = $('#date_to').val();

        $.ajax({
            url: 'fetch_filtered_data.php', // PHP file to handle filtering
            method: 'POST',
            data: { date_from: fromDate, date_to: toDate },
            success: function (response) {
                $('#filtered-results').html(response); // Show filtered results
            },
            error: function () {
                alert('Error fetching data.');
            }
        });
    });
});
var studentno = $("#Delete").val(); // or any dynamic value
var deleteButton = `
    <a href="delete_student.php?id=${id}" 
       class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to delete this student?')">
       Delete
    </a>
`;
$("#deleteContainer").html(deleteButton); // insert into your modal or container

// Check if the modal should be shown
<?php if ($showModal): ?>
            $(document).ready(function() {
                $('#messageModal').modal('show'); // Show the modal
            });
        <?php endif; ?>



    </script>
 
</body>
</html>
