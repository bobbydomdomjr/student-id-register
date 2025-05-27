<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all students
$sql_students = "SELECT * FROM student_registration ORDER BY id DESC";
$result_students = $conn->query($sql_students);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        #sidebar a:hover, #sidebar a.active {
            background-color: #495057;
        }
        #content {
            flex-grow: 1;
            padding: 20px;
        }
        th, td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-sm i {
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <h4 class="text-center py-3">Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="students.php" class="active"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="logout.php" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Content Area -->
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Student Management</h2>
                <a href="add_student.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Student</a>
            </div>

            <!-- Student Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Student No</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['studentno']); ?></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['firstname'] . ' ' .
                                        (!empty($row['middleinitial']) ? $row['middleinitial'] . '. ' : '') .
                                        $row['lastname']
                                    );
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                <td><?php echo htmlspecialchars($row['yearlevel']); ?></td>
                                <td>
                                    <!-- View Details Button -->
                                    <button class="btn btn-info btn-sm" 
                                            onclick="showDetails(
                                                '<?php echo htmlspecialchars($row['studentno']); ?>',
                                                '<?php echo htmlspecialchars($row['firstname'] . ' ' . (!empty($row['middleinitial']) ? $row['middleinitial'] . '. ' : '') . $row['lastname']); ?>',
                                                '<?php echo htmlspecialchars($row['email']); ?>',
                                                '<?php echo htmlspecialchars($row['gender']); ?>',
                                                '<?php echo htmlspecialchars($row['phone']); ?>',
                                                '<?php echo htmlspecialchars($row['course']); ?>',
                                                '<?php echo htmlspecialchars($row['yearlevel']); ?>',
                                                '<?php echo htmlspecialchars($row['block']); ?>',
                                                '<?php echo htmlspecialchars($row['dob']); ?>',
                                                '<?php echo htmlspecialchars($row['address']); ?>',
                                                '<?php echo htmlspecialchars($row['contactname']); ?>',
                                                '<?php echo htmlspecialchars($row['contactno']); ?>',
                                                '<?php echo htmlspecialchars($row['relationship']); ?>'
                                            )">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <a href="edit_student.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_student.php?id=<?php echo $row['id']; ?>" onclick="return confirmDelete()" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal to Preview Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="detailsModalLabel">Student Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Modal Content (Student Info) -->
                    <table class="table table-bordered">
                        <tr><th>Student No</th><td id="modal-studentno"></td></tr>
                        <tr><th>Full Name</th><td id="modal-fullname"></td></tr>
                        <tr><th>Email</th><td id="modal-email"></td></tr>
                        <tr><th>Gender</th><td id="modal-gender"></td></tr>
                        <tr><th>Phone</th><td id="modal-phone"></td></tr>
                        <tr><th>Course</th><td id="modal-course"></td></tr>
                        <tr><th>Year Level</th><td id="modal-yearlevel"></td></tr>
                        <tr><th>Block</th><td id="modal-block"></td></tr>
                        <tr><th>Date of Birth</th><td id="modal-dob"></td></tr>
                        <tr><th>Address</th><td id="modal-address"></td></tr>
                        <tr><th>Contact Name</th><td id="modal-contactname"></td></tr>
                        <tr><th>Contact No</th><td id="modal-contactno"></td></tr>
                        <tr><th>Relationship</th><td id="modal-relationship"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }
        function confirmDelete() {
            return confirm('Are you sure you want to delete this student?');
        }
        // Show Modal with Student Details
        function showDetails(studentno, fullname, email, gender, phone, course, yearlevel, block, dob, address, contactname, contactno, relationship) {
            document.getElementById('modal-studentno').innerText = studentno;
            document.getElementById('modal-fullname').innerText = fullname;
            document.getElementById('modal-email').innerText = email;
            document.getElementById('modal-gender').innerText = gender;
            document.getElementById('modal-phone').innerText = phone;
            document.getElementById('modal-course').innerText = course;
            document.getElementById('modal-yearlevel').innerText = yearlevel;
            document.getElementById('modal-block').innerText = block;
            document.getElementById('modal-dob').innerText = dob;
            document.getElementById('modal-address').innerText = address;
