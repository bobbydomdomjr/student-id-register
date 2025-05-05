<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch course-wise and monthly registration stats
$sql_course = "SELECT course, COUNT(*) AS count FROM student_registration GROUP BY course";
$result_course = $conn->query($sql_course);

// Check if query execution was successful
if (!$result_course) {
    die("Error executing query (course stats): " . $conn->error);
}

$sql_monthly = "SELECT DATE_FORMAT(registration_date, '%Y-%m') AS month, COUNT(*) AS count FROM student_registration GROUP BY month ORDER BY month";
$result_monthly = $conn->query($sql_monthly);

// Check if query execution was successful
if (!$result_monthly) {
    die("Error executing query (monthly stats): " . $conn->error);
}

// Fetch summary data
$sql_summary = "SELECT 
                    COUNT(*) AS total_students, 
                    COUNT(DISTINCT course) AS total_courses, 
                    COUNT(DISTINCT DATE_FORMAT(registration_date, '%Y-%m')) AS total_months
                FROM student_registration";
$result_summary = $conn->query($sql_summary);

// Check if query execution was successful
if (!$result_summary) {
    die("Error executing query (summary stats): " . $conn->error);
}

$summary = $result_summary->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        canvas {
            max-height: 400px;
        }
        .summary-card {
            text-align: center;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .summary-icon {
            font-size: 36px;
            margin-bottom: 10px;
            color: #17a2b8;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <h4 class="text-center py-3">Admin Panel</h4>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="students.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="logout.php" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Content Area -->
    <div id="content">
        <div class="container-fluid">
            <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Reports & Statistics</h2>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <i class="fas fa-users summary-icon"></i>
                        <h5>Total Students</h5>
                        <h4><?php echo $summary['total_students']; ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <i class="fas fa-book summary-icon"></i>
                        <h5>Total Courses</h5>
                        <h4><?php echo $summary['total_courses']; ?></h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <i class="fas fa-calendar-alt summary-icon"></i>
                        <h5>Monthly Registrations</h5>
                        <h4><?php echo $summary['total_months']; ?></h4>
                    </div>
                </div>
            </div>

            <!-- Chart Filters -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <select id="courseFilter" class="form-control">
                        <option value="">All Courses</option>
                        <?php
                        $result_course->data_seek(0);
                        while ($row = $result_course->fetch_assoc()) {
                            echo '<option value="' . $row['course'] . '">' . $row['course'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="month" id="monthFilter" class="form-control">
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h5>Course Enrollment</h5>
                    <canvas id="courseChart" style="display:none;"></canvas>
                    <p id="courseNoData" class="text-muted text-center" style="display:none;">No data available for this filter.</p>
                </div>
                <div class="col-md-6 mb-4">
                    <h5>Monthly Registrations</h5>
                    <canvas id="monthlyChart" style="display:none;"></canvas>
                    <p id="monthlyNoData" class="text-muted text-center" style="display:none;">No data available for this filter.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Course Data
        const courseData = <?php
            $data_course = [];
            $result_course->data_seek(0);
            while ($row = $result_course->fetch_assoc()) {
                $data_course[] = ['course' => $row['course'], 'count' => $row['count']];
            }
            echo json_encode($data_course);
        ?>;
        const courseLabels = courseData.map(item => item.course);
        const courseCounts = courseData.map(item => item.count);

        // Monthly Data
        const monthlyData = <?php
            $data_monthly = [];
            while ($row = $result_monthly->fetch_assoc()) {
                $data_monthly[] = ['month' => $row['month'], 'count' => $row['count']];
            }
            echo json_encode($data_monthly);
        ?>;
        const monthlyLabels = monthlyData.map(item => item.month);
        const monthlyCounts = monthlyData.map(item => item.count);

        // Course Chart
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        let courseChart = new Chart(courseCtx, {
            type: 'pie',
            data: {
                labels: courseLabels,
                datasets: [{
                    label: 'Course Enrollment',
                    data: courseCounts,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                    hoverOffset: 4
                }]
            }
        });

        // Monthly Registration Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        let monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Monthly Registrations',
                    data: monthlyCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: true
                }]
            }
        });

        // Chart Filters
        document.getElementById('courseFilter').addEventListener('change', filterData);
        document.getElementById('monthFilter').addEventListener('change', filterData);

        function filterData() {
            const courseFilter = document.getElementById('courseFilter').value;
            const monthFilter = document.getElementById('monthFilter').value;

            const filteredCourseData = courseData.filter(item => courseFilter === '' || item.course === courseFilter);
            const filteredMonthlyData = monthlyData.filter(item => monthFilter === '' || item.month.startsWith(monthFilter));

            // Handle course chart visibility
            if (filteredCourseData.length === 0) {
                document.getElementById('courseChart').style.display = 'none';
                document.getElementById('courseNoData').style.display = 'block';
            } else {
                document.getElementById('courseChart').style.display = 'block';
                document.getElementById('courseNoData').style.display = 'none';
            }

            // Handle monthly chart visibility
            if (filteredMonthlyData.length === 0) {
                document.getElementById('monthlyChart').style.display = 'none';
                document.getElementById('monthlyNoData').style.display = 'block';
            } else {
                document.getElementById('monthlyChart').style.display = 'block';
                document.getElementById('monthlyNoData').style.display = 'none';
            }

            updateChart(courseChart, filteredCourseData.map(item => item.course), filteredCourseData.map(item => item.count), 'Course Enrollment');
            updateChart(monthlyChart, filteredMonthlyData.map(item => item.month), filteredMonthlyData.map(item => item.count), 'Monthly Registrations');
        }

        function updateChart(chart, labels, data, label) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.data.datasets[0].label = label;
            chart.update();
        }
    </script>

</body>
</html>
