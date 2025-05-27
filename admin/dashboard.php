<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/init.php';

// Welcome message (once after login)
$welcome_message = $_SESSION['welcome_message'] ?? '';
unset($_SESSION['welcome_message']);

// Today's date in Y-m-d for SQL
$today = date('Y-m-d');

// Map DB statuses → front-end labels
$statusMap = [
    'pending'     => 'Waiting',
    'processing'  => 'Processing',
    'done'        => 'Completed',
    'no-show'     => 'No-show'
];
$dbStatuses = array_keys($statusMap);

// 1) Fetch summary stats (using DB statuses)
$total_today      = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE DATE(registration_date) = '$today'");
$total_all        = getCount($conn, "SELECT COUNT(*) FROM student_registration");
$total_waiting    = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='pending'");
$total_processing = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='processing'");
$total_completed  = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='done'");
$total_noshow     = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='no-show'");

// 2) Prepare data for 30-day status trend chart
$sql = "
  SELECT 
    DATE(registration_date) AS date, 
    status, 
    COUNT(*) AS cnt
  FROM student_registration
  WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    AND status IN ('" . implode("','", $dbStatuses) . "')
  GROUP BY DATE(registration_date), status
  ORDER BY DATE(registration_date)
";
$result = $conn->query($sql);

// collect rows
$rows = [];
if ($result) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }
}

// build full date axis
$datesMap = [];
foreach ($rows as $r) {
    $datesMap[$r['date']] = true;
}
$dates = array_keys($datesMap);
sort($dates);

// zero-fill counts
$counts = [];
foreach ($dbStatuses as $st) {
    $counts[$st] = array_fill_keys($dates, 0);
}
foreach ($rows as $r) {
    $counts[$r['status']][$r['date']] = (int)$r['cnt'];
}

// JSON for Chart.js: use front-end labels, but data in DB-order
$labelsJson = json_encode($dates);
$datasets = [];
foreach ($dbStatuses as $st) {
    $datasets[] = [
        'label' => $statusMap[$st],
        'data'  => array_values($counts[$st]),
        'fill'  => false
    ];
}
$datasetsJson = json_encode($datasets);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Panel</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons & Font Awesome -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body { margin:0; }
        .home-section { margin-left:250px; padding:2rem; background:#f8f9fa; min-height:100vh; transition: margin-left .3s; }
        .sidebar.open ~ .home-section { margin-left:80px; }
        @media (max-width:768px) { .sidebar {width:0!important;} .sidebar.open {width:250px;} .home-section {margin-left:0;} }
        .home-section .text { font-size:1.75rem; font-weight:600; color:#343a40; }
        #welcomeMessage { position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#28a745; color:#fff; padding:10px 20px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,.2); display:none; z-index:9999; }
        .stat-card { border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,.1); transition:transform .2s, box-shadow .2s; min-height:140px; }
        .stat-card:hover { transform:translateY(-5px); box-shadow:0 8px 24px rgba(0,0,0,.15); }
        .stat-icon { font-size:1.6rem; opacity:.85; }
        .stat-header { font-weight:600; font-size:1rem; display:flex; align-items:center; gap:.5rem; }
        .stat-number { font-size:2rem; font-weight:bold; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<section class="home-section">
    <div class="text">Dashboard</div>
    <hr>

    <?php if ($welcome_message): ?>
        <div id="welcomeMessage">🎉 <?= htmlspecialchars($welcome_message) ?></div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row g-3">
            <!-- Students Today -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-white bg-primary stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-calendar-day stat-icon"></i>Students Today</div>
                        <div class="stat-number mt-3"><?= $total_today ?></div>
                    </div>
                </div>
            </div>
            <!-- Total Students -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-white bg-secondary stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-users stat-icon"></i>Total Students</div>
                        <div class="stat-number mt-3"><?= $total_all ?></div>
                    </div>
                </div>
            </div>
            <!-- Waiting (pending) -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-dark bg-warning stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-user-clock stat-icon"></i><?= $statusMap['pending'] ?></div>
                        <div class="stat-number mt-3"><?= $total_waiting ?></div>
                    </div>
                </div>
            </div>
            <!-- Processing -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-white bg-success stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-spinner stat-icon"></i><?= $statusMap['processing'] ?></div>
                        <div class="stat-number mt-3"><?= $total_processing ?></div>
                    </div>
                </div>
            </div>
            <!-- Completed (done) -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-white bg-info stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-check-circle stat-icon"></i><?= $statusMap['done'] ?></div>
                        <div class="stat-number mt-3"><?= $total_completed ?></div>
                    </div>
                </div>
            </div>
            <!-- No-show -->
            <div class="col-sm-6 col-md-2">
                <div class="card text-white bg-danger stat-card">
                    <div class="card-body">
                        <div class="stat-header"><i class="fas fa-times-circle stat-icon"></i><?= $statusMap['no-show'] ?></div>
                        <div class="stat-number mt-3"><?= $total_noshow ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 30-day Status Trend Line Chart -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card p-3">
                    <h5>Status Trends (Last 30 Days)</h5>
                    <div style="position: relative; width: 100%; height: 400px;">
                        <canvas id="statusChart"></canvas>
                    </div>

                </div>
            </div>
        </div>

        <footer class="text-center text-muted mt-5">
            <small>&copy; 2025 Student Registration System</small>
        </footer>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const msg = document.getElementById('welcomeMessage');
        if (msg) {
            msg.style.display = 'block';
            setTimeout(() => msg.style.display = 'none', 3000);
        }
    });
</script>

<!-- Chart.js + date-fns adapter -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
    (function() {
        const labels   = <?= $labelsJson ?>;
        const datasets = <?= $datasetsJson ?>;

        new Chart(document.getElementById('statusChart').getContext('2d'), {
            type: 'line',
            data: { labels, datasets: datasets.map(ds => ({ ...ds, tension: 0.2, borderWidth: 2 })) },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'day', tooltipFormat: 'yyyy-MM-dd' },
                        title: { display: true, text: 'Date' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Count' }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', intersect: false }
                }
            }
        });
    })();
</script>
</body>
</html>
