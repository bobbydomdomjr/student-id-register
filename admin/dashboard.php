<?php
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login/index.php');
    exit;
}

$user_id         = $_SESSION['user_id'];
$username        = $_SESSION['username'];
$role            = $_SESSION['role'];
$welcome_message = $_SESSION['welcome_message'] ?? '';
unset($_SESSION['welcome_message']);

// Today’s date
$today = date('Y-m-d');

// Status‐map for registrations
$statusMap  = [
    'pending'    => 'Waiting',
    'processing' => 'Processing',
    'done'       => 'Completed',
    'no-show'    => 'No-show'
];
$dbStatuses = array_keys($statusMap);

// 1) Summary stats
$total_today      = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE DATE(registration_date) = '$today'");
$total_all        = getCount($conn, "SELECT COUNT(*) FROM student_registration");
$total_waiting    = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='pending'");
$total_processing = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='processing'");
$total_completed  = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='done'");
$total_noshow     = getCount($conn, "SELECT COUNT(*) FROM student_registration WHERE status='no-show'");

// 2) 30‐day status trend
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

$rows = [];
if ($result) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }
}

// Build date axis
$datesMap = [];
foreach ($rows as $r) {
    $datesMap[$r['date']] = true;
}
$dates = array_keys($datesMap);
sort($dates);

// Zero‐fill
$counts = [];
foreach ($dbStatuses as $st) {
    $counts[$st] = array_fill_keys($dates, 0);
}
foreach ($rows as $r) {
    $counts[$r['status']][$r['date']] = (int)$r['cnt'];
}

// JSON for Chart.js
$labelsJson   = json_encode($dates);
$datasets     = [];
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Panel</title>

    <!-- Bootstrap 5 -->
    <link href="./../dist/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Boxicons & Font Awesome -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />

    <style>
        body { margin: 0; }
        .home-section { margin-left: 250px; padding: 2rem; background: #f8f9fa; min-height: 100vh; transition: margin-left .3s; }
        .sidebar.open ~ .home-section { margin-left: 80px; }
        @media (max-width: 768px) {
            .sidebar { width: 0!important; }
            .sidebar.open { width: 250px; }
            .home-section { margin-left: 0; }
        }
        .home-section .text { font-size: 1.75rem; font-weight: 600; color: #343a40; }
        #welcomeMessage {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            background: #28a745; color: #fff; padding: 10px 20px; border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,.2); display: none; z-index: 9999;
        }
        .stat-card {
            border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.1);
            transition: transform .2s, box-shadow .2s; min-height: 140px;
        }
        .stat-card:hover {
            transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,.15);
        }
        .stat-icon { font-size: 1.6rem; opacity: .85; }
        .stat-header {
            font-weight: 600; font-size: 1rem; display: flex; align-items: center; gap: .5rem;
        }
        .stat-number { font-size: 2rem; font-weight: bold; }

        /* ─── Chat‐Bubble CSS ─────────────────────────────────────────── */
        .chat-bubble {
            max-width: 75%; padding: .5rem .75rem; border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); word-wrap: break-word;
        }
        .chat-bubble-sender {
            background-color: #0d6efd; color: #fff;
            border-bottom-right-radius: 0; border-bottom-left-radius: 1rem;
        }
        .chat-bubble-receiver {
            background-color: #e9ecef; color: #343a40;
            border-bottom-left-radius: 0; border-bottom-right-radius: 1rem;
        }
        .avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background-color: #6c757d; color: #fff;
            flex-shrink: 0;
        }
        .avatar-sender {
            background-color: #0d6efd;
        }
        .avatar-receiver {
            background-color: #6c757d;
        }

        /* “Go‐Down” button INSIDE chat box */
        #scroll-down-btn {
            position: absolute;
            bottom: 60px; /* place it just above the input area */
            right: 10px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<section class="home-section">
    <!-- Top Nav Bar -->
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
                    <span class="fw-semibold"><?= htmlspecialchars($username) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="../logout.php" onclick="return confirmLogout();">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mt-3">
        <!-- Students Today -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-white bg-primary stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-calendar-day stat-icon"></i> Students Today</div>
                    <div class="stat-number mt-3"><?= $total_today ?></div>
                </div>
            </div>
        </div>
        <!-- Total Students -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-white bg-secondary stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-users stat-icon"></i> Total Students</div>
                    <div class="stat-number mt-3"><?= $total_all ?></div>
                </div>
            </div>
        </div>
        <!-- Waiting -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-dark bg-warning stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-user-clock stat-icon"></i> <?= $statusMap['pending'] ?></div>
                    <div class="stat-number mt-3"><?= $total_waiting ?></div>
                </div>
            </div>
        </div>
        <!-- Processing -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-white bg-success stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-spinner stat-icon"></i> <?= $statusMap['processing'] ?></div>
                    <div class="stat-number mt-3"><?= $total_processing ?></div>
                </div>
            </div>
        </div>
        <!-- Completed -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-white bg-info stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-check-circle stat-icon"></i> <?= $statusMap['done'] ?></div>
                    <div class="stat-number mt-3"><?= $total_completed ?></div>
                </div>
            </div>
        </div>
        <!-- No-show -->
        <div class="col-sm-6 col-md-2">
            <div class="card text-white bg-danger stat-card">
                <div class="card-body">
                    <div class="stat-header"><i class="fas fa-times-circle stat-icon"></i> <?= $statusMap['no-show'] ?></div>
                    <div class="stat-number mt-3"><?= $total_noshow ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 30‐day Status Trend Chart -->
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
        <!-- User Registrations (This Month) -->
        <div class="col-md-6 mb-4">
            <div class="card p-3 h-100">
                <h5>User Registrations (This Month)</h5>
                <div style="position: relative; width: 100%; height: 300px;">
                    <canvas id="registrationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Queue Activity (Last 14 Days) -->
        <div class="col-md-6 mb-4">
            <div class="card p-3 h-100">
                <h5>Queue Activity (Last 14 Days)</h5>
                <div style="position: relative; width: 100%; height: 300px;">
                    <canvas id="queueActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat Button & Chat Box -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Chat Button (bottom right) -->
    <div id="chat-button" style="position:fixed;bottom:20px;right:20px;cursor:pointer;z-index:9999;">
        <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center position-relative" id="open-chat" style="width: 50px; height: 50px;">
            <i class="fas fa-comments"></i>
            <span id="unread-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle p-1" style="font-size: 0.7rem; display: none;">0</span>
        </button>
    </div>

    <!-- Chat Box (fixed above the chat button) -->
    <div id="chat-box" class="card shadow border-0" style="display:none;position:fixed;bottom:80px;right:20px;width:300px;z-index:9999;">
        <div class="card-header bg-primary text-white py-2 px-3 d-flex justify-content-between align-items-center">
            <span>Live Chat</span>
            <button type="button" class="btn-close btn-close-white btn-sm" id="close-chat"></button>
        </div>

        <!-- Go-Down button is INSIDE this chat box -->
        <button id="scroll-down-btn" class="btn btn-secondary btn-sm">
            <i class="fas fa-chevron-down"></i>
            <span id="new-count">0</span>
        </button>

        <div class="card-body p-2 position-relative" id="chat-messages" style="height:300px;overflow-y:auto;"></div>
        <div class="card-footer p-2">
            <form id="chat-form" class="d-flex">
                <input type="text" id="chat-input" class="form-control me-2" placeholder="Type a message…" required>
                <button type="submit" class="btn btn-primary btn-sm">Send</button>
            </form>
        </div>
    </div>

    <footer class="text-center text-muted mt-5">
        <small>&copy; 2025 Student ID Registration System | Bobby Domdom Jr</small>
    </footer>
</section>

<!-- Bootstrap JS -->
<script src="./../dist/bootstrap/js/bootstrap.bundle.min.js"></script>
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
        // ─── Status Trends (Last 30 Days) ──────────────────────────────
        const labels = <?= $labelsJson ?> || [];
        const rawDatasets = <?= $datasetsJson ?> || [];

        const defaultColors = [
            '#3366CC', '#DC3912', '#FF9900', '#109618',
            '#990099', '#0099C6', '#DD4477', '#66AA00'
        ];

        const statusDatasets = rawDatasets.map((ds, i) => ({
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

        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'line',
            data: { labels, datasets: statusDatasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'PPP',
                            displayFormats: { day: 'MMM dd' },
                        },
                        title: { display: true, text: 'Date' },
                        grid: { color: '#eee', borderColor: '#ccc' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Count' },
                        grid: { color: '#eee', borderColor: '#ccc' }
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

        // ─── User Registrations (This Month) ──────────────────────────
        // (Dummy data for illustration; replace with your real data if desired)
        const regCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(regCtx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Registrations',
                    data: [<?= rand(5,20) ?>, <?= rand(10,30) ?>, <?= rand(8,25) ?>, <?= rand(12,35) ?>],
                    backgroundColor: defaultColors[1]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: { display: true, text: 'Week' },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Users' },
                        grid: { color: '#eee', borderColor: '#ccc' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        titleFont: { weight: 'bold' },
                        callbacks: {
                            label: ctx => `Users: ${ctx.parsed.y}`
                        }
                    }
                }
            }
        });

        // ─── Queue Activity (Last 14 Days) ─────────────────────────────
        // (Dummy data for illustration; replace with real data if desired)
        const queueCtx = document.getElementById('queueActivityChart').getContext('2d');
        const queueLabels = Array.from({ length: 14 }, (_, i) => {
            const d = new Date();
            d.setDate(d.getDate() - (13 - i));
            return d.toISOString().slice(0, 10);
        });
        const queueData = queueLabels.map(() => Math.floor(Math.random() * 80) + 10);

        new Chart(queueCtx, {
            type: 'line',
            data: {
                labels: queueLabels,
                datasets: [{
                    label: 'Queue Events',
                    data: queueData,
                    borderColor: defaultColors[3],
                    backgroundColor: 'rgba(25,135,84,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                parsing: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'PPP',
                            displayFormats: { day: 'MMM dd' }
                        },
                        title: { display: true, text: 'Date' },
                        grid: { color: '#eee', borderColor: '#ccc' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Events' },
                        grid: { color: '#eee', borderColor: '#ccc' }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        titleFont: { weight: 'bold' },
                        callbacks: {
                            label: ctx => `Events: ${ctx.parsed.y}`
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

<script>
    // ─── Chat JS (only scroll if user is already at bottom; otherwise show “go-down” button;
    //      but if the user sends a new message, always scroll to bottom) ─────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const chatButton     = document.getElementById('open-chat');
        const chatBox        = document.getElementById('chat-box');
        const closeChat      = document.getElementById('close-chat');
        const chatForm       = document.getElementById('chat-form');
        const chatInput      = document.getElementById('chat-input');
        const chatMessages   = document.getElementById('chat-messages');
        const unreadCount    = document.getElementById('unread-count');
        const scrollDownBtn  = document.getElementById('scroll-down-btn');
        const newCountSpan   = document.getElementById('new-count');

        let isChatOpen       = false;
        let lastMessageCount = 0;
        let newMessageCount  = 0;

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function updateScrollDownUI() {
            if (newMessageCount > 0) {
                newCountSpan.textContent = newMessageCount;
                scrollDownBtn.style.display = 'block';
            } else {
                scrollDownBtn.style.display = 'none';
            }
        }

        // Whenever user scrolls manually, check if they're now at bottom
        chatMessages.addEventListener('scroll', () => {
            if (chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 10) {
                // At bottom: hide the button and reset count
                newMessageCount = 0;
                updateScrollDownUI();
            }
        });

        function fetchMessages(autoScrollIfAtBottom = true) {
            // Detect if we are currently scrolled to the bottom (within 10px)
            const atBottom = chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 10;

            fetch('../chat/fetch_messages.php')
                .then(response => response.json())
                .then(data => {
                    // Count how many chat-bubble elements currently exist
                    const oldCount = lastMessageCount;
                    chatMessages.innerHTML = data.messages;
                    const bubbles = chatMessages.querySelectorAll('.chat-bubble');
                    const currentCount = bubbles.length;

                    // If new messages arrived while scrolled up:
                    if (!atBottom && currentCount > oldCount) {
                        newMessageCount += (currentCount - oldCount);
                    }

                    // Only auto-scroll if we were at the bottom before fetching (and autoScrollIfAtBottom is true)
                    if (autoScrollIfAtBottom && atBottom) {
                        scrollToBottom();
                        newMessageCount = 0; // reset
                    }

                    lastMessageCount = currentCount;
                    updateScrollDownUI();

                    // Update the small unread badge on the chat button if chat is closed
                    if (!isChatOpen && data.unread > 0) {
                        unreadCount.textContent = data.unread;
                        unreadCount.style.display = 'inline-block';
                    } else {
                        unreadCount.style.display = 'none';
                    }
                });
        }

        // Send a new message
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
                        // After sending, fetch and force scroll to bottom
                        fetchMessages(false); // first update content, but don't auto-check old atBottom logic
                        scrollToBottom();
                        newMessageCount = 0;
                        updateScrollDownUI();
                    });
            }
        });

        // Open/close chat box
        chatButton.addEventListener('click', function () {
            isChatOpen = !isChatOpen;
            chatBox.style.display = isChatOpen ? 'block' : 'none';
            if (isChatOpen) {
                unreadCount.style.display = 'none';
                fetchMessages(true);
            }
        });

        closeChat.addEventListener('click', function () {
            chatBox.style.display = 'none';
            isChatOpen = false;
        });

        // “Go-Down” button clicked
        scrollDownBtn.addEventListener('click', () => {
            scrollToBottom();
            newMessageCount = 0;
            updateScrollDownUI();
        });

        // Initialize: count existing messages and scroll to bottom
        setTimeout(() => {
            fetchMessages(true);
        }, 100);

        // Poll every 3 seconds
        setInterval(() => fetchMessages(true), 3000);
    });

    // Update bell badge every 10 seconds
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
    setInterval(updateNotificationBadge, 10000);
    document.addEventListener('DOMContentLoaded', updateNotificationBadge);

    function confirmLogout() {
        return confirm("Are you sure you want to logout?");
    }
</script>
</body>
</html>
