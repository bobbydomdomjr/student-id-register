<?php
// admin/reports.php
require_once __DIR__ . '/../includes/init.php';

// 1) Summary counts
$today      = date('Y-m-d');
$totalAll   = getCount($conn, "SELECT COUNT(*) FROM student_registration");
$totalToday = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE DATE(registration_date) = '$today'");
$pending    = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='pending'");
$processing = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='processing'");
$done       = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='done'");
$noshow     = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='no-show'");

// 2) Status filter
$allowed = ['all','pending','processing','done','no-show'];
$status  = in_array($_GET['status'] ?? 'all', $allowed) ? $_GET['status'] : 'all';

// 3) Table data
if ($status === 'all') {
    $stmt = $conn->prepare("SELECT * FROM student_registration ORDER BY registration_date DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM student_registration WHERE status = ? ORDER BY registration_date DESC");
    $stmt->bind_param('s', $status);
}
$stmt->execute();
$regs = $stmt->get_result();

// 4) Logs
$logRes = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50");

// 5) Build course-by-status
$courseByStatus = [];
foreach (array_merge(['all'], $allowed) as $st) {
    $courseByStatus[$st] = [];
}
$rs = $conn->query("
  SELECT status, course, COUNT(*) AS cnt
    FROM student_registration
   GROUP BY status, course
");
while ($r = $rs->fetch_assoc()) {
    $courseByStatus[$r['status']][$r['course']] = (int)$r['cnt'];
}
# Also build the 'all' bucket
foreach ($courseByStatus as $st => $arr) {
    if ($st==='all') continue;
    foreach ($arr as $course => $cnt) {
        $courseByStatus['all'][$course] = ($courseByStatus['all'][$course] ?? 0) + $cnt;
    }
}

// 6) Build monthly-by-status
$monthlyByStatus = [];
foreach (array_merge(['all'], $allowed) as $st) {
    $monthlyByStatus[$st] = [];
}
$rs2 = $conn->query("
  SELECT status, DATE_FORMAT(registration_date,'%Y-%m') AS m, COUNT(*) AS cnt
    FROM student_registration
   GROUP BY status, m
   ORDER BY m
");
while ($r = $rs2->fetch_assoc()) {
    $monthlyByStatus[$r['status']][$r['m']] = (int)$r['cnt'];
}
foreach ($monthlyByStatus as $st => $arr) {
    if ($st==='all') continue;
    foreach ($arr as $m => $cnt) {
        $monthlyByStatus['all'][$m] = ($monthlyByStatus['all'][$m] ?? 0) + $cnt;
    }
}

// 7) Status breakdown data
$statusChartData = [
    ['label'=>'Pending',    'count'=>$pending,    'color'=>'#ffc107'],
    ['label'=>'Processing', 'count'=>$processing, 'color'=>'#28a745'],
    ['label'=>'Completed',  'count'=>$done,       'color'=>'#17a2b8'],
    ['label'=>'No-show',    'count'=>$noshow,     'color'=>'#dc3545'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reports & Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { margin:0; }
        .home-section { margin-left:250px; padding:2rem; background:#f8f9fa; min-height:100vh; transition: margin-left .3s; }
        .sidebar.open ~ .home-section { margin-left:80px; }
        @media (max-width:768px) { .sidebar {width:0!important;} .sidebar.open {width:250px;} .home-section {margin-left:0;} }
        .stat-card { border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,.1); }
        .stat-icon { font-size:1.5rem; opacity:.8; }
        .chart-container { max-width:700px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<section class="home-section p-4">
    <h2>Reports &amp; Logs</h2><hr>

    <ul class="nav nav-tabs mb-4" id="tabs">
        <li class="nav-item"><a class="nav-link active" data-bs-target="#reportsTab">Reports</a></li>
        <li class="nav-item"><a class="nav-link"       data-bs-target="#logsTab">Logs</a></li>
    </ul>

    <div class="tab-content">

        <!-- REPORTS -->
        <div class="tab-pane fade show active text-center" id="reportsTab">

            <!-- Summary -->
            <div class="row g-3 mb-4">
                <?php foreach ([
                                   ['label'=>'Total','count'=>$totalAll,'bg'=>'primary','icon'=>'users'],
                                   ['label'=>'Today','count'=>$totalToday,'bg'=>'secondary','icon'=>'calendar-day'],
                                   ['label'=>'Pending','count'=>$pending,'bg'=>'warning','icon'=>'hourglass-start'],
                                   ['label'=>'Processing','count'=>$processing,'bg'=>'success','icon'=>'spinner'],
                                   ['label'=>'Completed','count'=>$done,'bg'=>'info','icon'=>'check-circle'],
                                   ['label'=>'No-show','count'=>$noshow,'bg'=>'danger','icon'=>'user-times'],
                               ] as $c): ?>
                    <div class="col-md-2">
                        <div class="card text-white bg-<?= $c['bg'] ?> stat-card p-3">
                            <div><i class="fas fa-<?= $c['icon'] ?> stat-icon"></i> <?= $c['label'] ?></div>
                            <div class="h3"><?= $c['count'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Table Filter -->
            <div class="d-flex align-items-center just mb-3">
                <label class="me-2">Status:</label>
                <select id="statusFilter" class="form-select w-auto">
                    <?php foreach ($allowed as $opt): ?>
                        <option value="<?= $opt ?>" <?= $status===$opt?'selected':'' ?>>
                            <?= $opt==='all'?'All':ucfirst($opt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="table-responsive mb-5">
                <table class="table table-striped">
                    <thead class="table-dark">
                    <tr><th>#</th><th>Student No.</th><th>Name</th><th>Status</th><th>When</th></tr>
                    </thead>
                    <tbody>
                    <?php $i=1; while($r=$regs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($r['studentno']) ?></td>
                            <td><?= htmlspecialchars("{$r['lastname']}, {$r['firstname']}") ?></td>
                            <td><?= ucfirst($r['status']) ?></td>
                            <td><?= $r['registration_date'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Status Breakdown -->
            <h5>Status Breakdown</h5>
            <div class="d-flex justify-content-center mb-5">
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Course Enrollment -->
            <h5>Course Enrollment</h5>
            <div class="d-flex align-items-center justify-content-center mb-2">
                <label class="me-2">Status:</label>
                <select id="courseStatusFilter" class="form-select w-auto me-4">
                    <?php foreach ($allowed as $opt): ?>
                        <option value="<?= $opt ?>" <?= $status===$opt?'selected':'' ?>>
                            <?= $opt==='all'?'All':ucfirst($opt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="me-2">Course:</label>
                <select id="courseFilter" class="form-select w-auto">
                    <option value="">All</option>
                    <?php foreach (array_keys($courseByStatus['all']) as $c): ?>
                        <option><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex justify-content-center mb-5">
                <div class="chart-container">
                    <canvas id="courseChart"></canvas>
                    <p id="courseNoData" class="text-center text-muted" style="display:none;">No data.</p>
                </div>
            </div>

            <!-- Monthly Registrations -->
            <h5>Monthly Registrations</h5>
            <div class="d-flex align-items-center justify-content-center mb-2">
                <label class="me-2">Status:</label>
                <select id="monthStatusFilter" class="form-select w-auto me-4">
                    <?php foreach ($allowed as $opt): ?>
                        <option value="<?= $opt ?>" <?= $status===$opt?'selected':'' ?>>
                            <?= $opt==='all'?'All':ucfirst($opt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="me-2">Month:</label>
                <input type="month" id="monthFilter" class="form-control w-auto">
            </div>
            <div class="d-flex justify-content-center mb-5">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                    <p id="monthlyNoData" class="text-center text-muted" style="display:none;">No data.</p>
                </div>
            </div>
        </div>

        <!-- LOGS -->
        <div class="tab-pane fade" id="logsTab">
            <h5>Recent Notifications</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr><th>#</th><th>User ID</th><th>Message</th><th>When</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php $i=1; while($log=$logRes->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $log['user_id'] ?></td>
                            <td><?= htmlspecialchars($log['message']) ?></td>
                            <td><?= $log['created_at'] ?></td>
                            <td><?= ucfirst($log['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- Chart.js + adapter -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
    // Tab switch
    document.querySelectorAll('#tabs .nav-link').forEach(l => {
        l.onclick = () => {
            document.querySelectorAll('#tabs .nav-link').forEach(x=>x.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(x=>x.classList.remove('show','active'));
            l.classList.add('active');
            document.querySelector(l.dataset.bsTarget).classList.add('show','active');
        };
    });

    // Table filter
    document.getElementById('statusFilter').onchange = function(){
        window.location.href = 'reports.php?status=' + encodeURIComponent(this.value);
    };

    // Data for JS
    const statusChartData = <?= json_encode($statusChartData) ?>;
    const courseByStatus   = <?= json_encode($courseByStatus) ?>;
    const monthlyByStatus  = <?= json_encode($monthlyByStatus) ?>;

    // Status Breakdown (bar)
    const sc = statusChartData;
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: sc.map(d=>d.label),
            datasets: [{
                data: sc.map(d=>d.count),
                backgroundColor: sc.map(d=>d.color),
                label: 'Status Counts',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Course (pie) with dual filters
    const courseChart = new Chart(
        document.getElementById('courseChart'), {
            type: 'pie',
            data: { labels:[], datasets:[{ data:[], backgroundColor: [] }] }
        });

    function updateCourseChart(){
        const st = document.getElementById('courseStatusFilter').value;
        const cf = document.getElementById('courseFilter').value;
        const dataObj = courseByStatus[st] || {};
        const labels = [], data=[];
        for (let c in dataObj){
            if (!cf||cf===c){
                labels.push(c);
                data.push(dataObj[c]);
            }
        }
        if (!data.length){
            courseChart.canvas.style.display='none'; document.getElementById('courseNoData').style.display='block';
        } else {
            courseChart.canvas.style.display='block'; document.getElementById('courseNoData').style.display='none';
            courseChart.data.labels = labels;
            courseChart.data.datasets[0].data = data;
            courseChart.data.datasets[0].backgroundColor = labels.map((_,i)=>['#007bff','#28a745','#ffc107','#dc3545','#17a2b8'][i%5]);
            courseChart.update();
        }
    }
    document.getElementById('courseStatusFilter').onchange = updateCourseChart;
    document.getElementById('courseFilter').onchange = updateCourseChart;
    updateCourseChart();

    // Monthly (line) with dual filters
    const monthlyChart = new Chart(
        document.getElementById('monthlyChart'), {
            type: 'line',
            data: { labels:[], datasets:[{ label:'Regs', data:[], fill:true }] },
            options: {
                responsive:true,
                scales:{
                    x:{ type:'time', time:{ parser:'yyyy-MM', unit:'month', tooltipFormat:'yyyy-MM' } },
                    y:{ beginAtZero:true }
                }
            }
        });

    function updateMonthlyChart(){
        const st = document.getElementById('monthStatusFilter').value;
        const mf = document.getElementById('monthFilter').value;
        const dataObj = monthlyByStatus[st] || {};
        const labels = [], data=[];
        for (let m in dataObj){
            if (!mf||m.startsWith(mf)){
                labels.push(m);
                data.push(dataObj[m]);
            }
        }
        if (!data.length){
            monthlyChart.canvas.style.display='none'; document.getElementById('monthlyNoData').style.display='block';
        } else {
            monthlyChart.canvas.style.display='block'; document.getElementById('monthlyNoData').style.display='none';
            monthlyChart.data.labels = labels;
            monthlyChart.data.datasets[0].data = data;
            monthlyChart.update();
        }
    }
    document.getElementById('monthStatusFilter').onchange = updateMonthlyChart;
    document.getElementById('monthFilter').onchange = updateMonthlyChart;
    updateMonthlyChart();
</script>
</body>
</html>
