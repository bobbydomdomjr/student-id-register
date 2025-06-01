<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/init.php';


$user_id = $_SESSION['admin']['user_id'] ?? null;
$username = $_SESSION['admin']['username'] ?? null;
$role = $_SESSION['admin']['role'] ?? null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
<!-- Top Navigation Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-center px-3 py-2 border-bottom bg-white shadow-sm sticky-top" style="z-index: 1030;">
  <!-- Left: Dashboard Title -->
  <div class="h5 mb-0 text-primary">📊 Dashboard</div>

  <!-- Right: Notification Bell + Toast + User Dropdown -->
  <div class="d-flex align-items-center gap-3">

    <!-- Notification Bell -->
    <div class="position-relative">
      <button id="notificationButton" class="btn btn-light rounded-circle shadow-sm position-relative">
        <i class="fas fa-bell text-primary"></i>
        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; display: none;">
          0
        </span>
      </button>
    </div>

    <!-- Toast (Welcome Message) -->
    <?php if (!empty($welcome_message)): ?>
      <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div id="welcomeToast" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              🎉 <?= htmlspecialchars($welcome_message) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const toastEl = document.getElementById('welcomeToast');
          if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, { delay: 5000 });
            toast.show();
          }
        });
      </script>
    <?php endif; ?>

    <!-- User Profile Dropdown -->
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-user-circle fa-lg me-2 text-primary"></i>
        <span class="fw-semibold"><?= htmlspecialchars($_SESSION['admin'] ?? 'Admin') ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</div>


<p>

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
<!-- Charts Side by Side -->
<div class="row mt-4">
  <!-- User Registrations Bar Chart -->
  <div class="col-md-6 mb-4">
    <div class="card p-3 h-100">
      <h5>User Registrations (This Month)</h5>
      <div style="position: relative; width: 100%; height: 300px;">
        <canvas id="registrationChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Queue Activity Area Chart -->
  <div class="col-md-6 mb-4">
    <div class="card p-3 h-100">
      <h5>Queue Activity (Last 14 Days)</h5>
      <div style="position: relative; width: 100%; height: 300px;">
        <canvas id="queueActivityChart"></canvas>
      </div>
    </div>
  </div>
</div>


<!-- Floating Chat Button -->
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<<!-- Chat Button -->
<div id="chat-button" style="position:fixed;bottom:20px;right:20px;cursor:pointer;z-index:9999;">
  <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center position-relative" id="open-chat" style="width: 50px; height: 50px;">
    <i class="fas fa-comments"></i>
    <span id="unread-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1" style="font-size: 0.7rem; display: none;">0</span>
  </button>
</div>

<!-- Chat Box -->
<div id="chat-box" class="card shadow border-0" style="display:none;position:fixed;bottom:80px;right:20px;width:300px;z-index:9999;">
  <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
    <span>Live Chat</span>
    <button type="button" class="btn-close btn-close-white btn-sm" id="close-chat"></button>
  </div>
  <div class="card-body p-2" id="chat-messages" style="height:300px;overflow-y:auto;"></div>
  <div class="card-footer p-2">
    <form id="chat-form" class="d-flex">
      <input type="text" id="chat-input" class="form-control me-2" placeholder="Type a message..." required>
      <button type="submit" class="btn btn-primary btn-sm">Send</button>
    </form>
  </div>
</div>



    <footer class="text-center text-muted mt-5">
        <small>&copy; 2025 Student ID Registration System | Bobby Domdom Jr</small>
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

<<!-- Chart.js + date-fns adapter -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

  <script>
document.addEventListener('DOMContentLoaded', function () {
  const chatButton = document.getElementById('open-chat');
  const chatBox = document.getElementById('chat-box');
  const closeChat = document.getElementById('close-chat');
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const chatMessages = document.getElementById('chat-messages');
  const unreadCount = document.getElementById('unread-count');

  let isChatOpen = false;

  function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

function fetchMessages() {
  fetch('../chat/fetch_messages.php')
    .then(response => response.json())
    .then(data => {
      chatMessages.innerHTML = data.messages;
      scrollToBottom();

      // Show or hide unread badge
      if (!isChatOpen && data.unread > 0) {
        unreadCount.textContent = data.unread;
        unreadCount.style.display = 'inline-block';
      } else {
        unreadCount.style.display = 'none';
      }
    });
}

  chatForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const message = chatInput.value.trim();
    if (message !== '') {
      fetch('../chat/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message })
      })
      .then(() => {
        chatInput.value = '';
        fetchMessages();
      });
    }
  });

  chatButton.addEventListener('click', function () {
    isChatOpen = !isChatOpen;
    chatBox.style.display = isChatOpen ? 'block' : 'none';
    if (isChatOpen) {
      unreadCount.style.display = 'none';
      fetchMessages();
    }
  });

  closeChat.addEventListener('click', function () {
    chatBox.style.display = 'none';
    isChatOpen = false;
  });

  // Polling
  setInterval(fetchMessages, 3000);
});
</script>


<script>
  (function() {
    const labels = <?= $labelsJson ?> || [];
    const rawDatasets = <?= $datasetsJson ?> || [];

    // Define a default color palette for datasets if not provided
    const defaultColors = [
      '#3366CC', '#DC3912', '#FF9900', '#109618',
      '#990099', '#0099C6', '#DD4477', '#66AA00'
    ];

    // Map datasets and enrich with default styles
    const datasets = rawDatasets.map((ds, i) => ({
      ...ds,
      tension: 0.2,
      borderWidth: 2,
      fill: false,
      borderColor: ds.borderColor || defaultColors[i % defaultColors.length],
      backgroundColor: ds.backgroundColor || defaultColors[i % defaultColors.length],
      pointRadius: 4,
      pointHoverRadius: 7,
      pointHoverBackgroundColor: ds.borderColor || defaultColors[i % defaultColors.length],
      pointHoverBorderColor: '#fff',
      pointHoverBorderWidth: 2,
      cubicInterpolationMode: 'monotone',
    }));

    const ctx = document.getElementById('statusChart').getContext('2d');

    new Chart(ctx, {
      type: 'line',
      data: { labels, datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          duration: 800,
          easing: 'easeOutQuart'
        },
        scales: {
          x: {
            type: 'time',
            time: {
              unit: 'day',
              tooltipFormat: 'PPP', // pretty formatted date, e.g. May 30, 2025
              displayFormats: {
                day: 'MMM dd',
              },
            },
            title: { display: true, text: 'Date' },
            grid: {
              color: '#eee',
              borderColor: '#ccc',
            }
          },
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Count' },
            grid: {
              color: '#eee',
              borderColor: '#ccc',
            }
          }
        },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, padding: 15 },
            onClick: (e, legendItem, legend) => {
              const index = legendItem.datasetIndex;
              const chart = legend.chart;
              const meta = chart.getDatasetMeta(index);
              // Toggle visibility
              meta.hidden = meta.hidden === null ? !chart.data.datasets[index].hidden : null;
              chart.update();
            }
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0,0,0,0.7)',
            titleFont: { weight: 'bold' },
            callbacks: {
              label: ctx => {
                const label = ctx.dataset.label || '';
                return `${label}: ${ctx.parsed.y}`;
              }
            }
          }
        },
        interaction: {
          mode: 'nearest',
          intersect: false
        }
      }
    });
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'line',
    data: {
      labels: [...Array(30).keys()].map(i => `Day ${i+1}`),
      datasets: [{
        label: 'Active',
        data: Array.from({ length: 30 }, () => Math.floor(Math.random() * 50 + 10)),
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        fill: true,
        tension: 0.4
      }]
    },
    options: { responsive: true }
  });

  const registrationChart = new Chart(document.getElementById('registrationChart'), {
    type: 'bar',
    data: {
      labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
      datasets: [{
        label: 'Users',
        data: [15, 22, 13, 30],
        backgroundColor: '#6610f2'
      }]
    }
  });

  const queueActivityChart = new Chart(document.getElementById('queueActivityChart'), {
    type: 'line',
    data: {
      labels: [...Array(14).keys()].map(i => `Day ${i+1}`),
      datasets: [{
        label: 'Queue Events',
        data: Array.from({ length: 14 }, () => Math.floor(Math.random() * 80)),
        borderColor: '#198754',
        backgroundColor: 'rgba(25,135,84,0.1)',
        fill: true,
        tension: 0.3
      }]
    }
  });



</script>
<script>
function updateNotificationBadge() {
  fetch('../chat/fetch_messages.php')
    .then(response => response.json())
    .then(data => {
      const badge = document.getElementById('notificationBadge');
      if (data.unread > 0) {
        badge.textContent = data.unread;
        badge.style.display = 'inline-block';
      } else {
        badge.style.display = 'none';
      }
    });
}

// Check every 10 seconds
setInterval(updateNotificationBadge, 10000);
document.addEventListener('DOMContentLoaded', updateNotificationBadge);
</script>

<script>
function confirmLogout() {
  return confirm("Are you sure you want to logout?");
}
</script>

</body>
</html>
