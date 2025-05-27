<?php
// staff/dashboard.php
session_start();
require_once('../db.php');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    header('Location: ./login/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="../dist/axios/axios.min.js"></script>
  <style>
    body { display:flex; min-height:100vh; }
    #sidebar { width:240px; background:#343a40; color:#fff; }
    #sidebar .nav-link { color:#ddd; }
    #sidebar .nav-link.active { background:#495057; color:#fff; }
    #content { flex:1; padding:20px; background:#f4f6f9; }
    .card-hover:hover { transform:translateY(-4px); box-shadow:0 6px 12px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
  <!-- Sidebar unchanged -->
  <nav id="sidebar" class="d-flex flex-column p-3">
    <a href="#" class="d-flex align-items-center mb-3 text-white text-decoration-none">
      <i class='bx bx-id-card bx-sm'></i><span class="fs-4 ms-2">Staff Panel</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li><a href="dashboard.php" class="nav-link active"><i class='bx bx-grid-alt'></i> Dashboard</a></li>
      <li><a href="queue_manager.php" class="nav-link"><i class='bx bx-skip-next'></i> Queue Handling</a></li>
      <li><a href="registration.php" class="nav-link"><i class='bx bx-pencil'></i> Registration</a></li>
      <li><a href="search.php" class="nav-link"><i class='bx bx-search'></i> Search</a></li>
      <li><a href="picture_upload.php" class="nav-link"><i class='bx bx-camera'></i> Photos</a></li>
      <li><a href="logout.php" class="nav-link"><i class='bx bx-log-out'></i> Logout</a></li>
    </ul>
  </nav>

  <div id="content" class="container-fluid">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['admin']) ?></h2>
    <div class="row g-4 mt-3">

      <!-- Now Serving -->
      <div class="col-md-3">
        <div id="now-serving-card" class="card card-hover text-center p-3">
          <i class='bx bx-skip-next bx-lg'></i>
          <h5 class="mt-2">Now Serving</h5>
          <p class="mb-1 name text-muted">Loading…</p>
          <small class="text-muted studentno"></small>
          <div class="d-flex justify-content-center gap-2 mt-2">
            <button id="notify-btn" class="btn btn-sm btn-warning" style="display:none">
              Notify
            </button>
            <button id="done-btn" class="btn btn-sm btn-success" style="display:none">
              Done
            </button>
          </div>
        </div>
      </div>

      <!-- Call Next -->
      <div class="col-md-3">
        <div id="call-next-card" class="card card-hover text-center p-3">
          <i class='bx bx-play-circle bx-lg'></i>
          <h5 class="mt-2">Call Next</h5>
          <p class="mb-1 next-name text-muted">Loading…</p>
          <small class="text-muted next-studentno"></small>
          <button id="call-btn" class="btn btn-sm btn-primary mt-2" style="display:none">
            Call &amp; Process
          </button>
        </div>
      </div>

      <!-- Status Cards -->
      <div class="col-md-2">
        <div class="card card-hover text-center p-3">
          <h6>Waiting</h6>
          <span id="count-pending" class="badge bg-warning fs-4">–</span>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card card-hover text-center p-3">
          <h6>Processing</h6>
          <span id="count-processing" class="badge bg-info fs-4">–</span>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card card-hover text-center p-3">
          <h6>Completed</h6>
          <span id="count-done" class="badge bg-success fs-4">–</span>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Polling data
    function refreshDashboard() {
      axios.get('fetch_dashboard.php')
        .then(({ data }) => {
          // Update counts…
          document.getElementById('count-pending').textContent    = data.counts.pending;
          document.getElementById('count-processing').textContent = data.counts.processing;
          document.getElementById('count-done').textContent       = data.counts.done;

          // Now Serving card
          const svcName  = document.querySelector('#now-serving-card .name');
          const svcNo    = document.querySelector('#now-serving-card .studentno');
          const notifyBtn = document.getElementById('notify-btn');
          const doneBtn   = document.getElementById('done-btn');

          if (data.serving) {
            svcName.textContent = data.serving.firstname + ' ' + data.serving.lastname;
            svcName.classList.remove('text-muted');
            svcNo.textContent   = data.serving.studentno;

            // show & wire buttons
            notifyBtn.style.display = 'inline-block';
            notifyBtn.dataset.studentno = data.serving.studentno;

            doneBtn.style.display = 'inline-block';
            doneBtn.dataset.studentno = data.serving.studentno;
          } else {
            svcName.textContent = 'None';
            svcName.classList.add('text-muted');
            svcNo.textContent = '';
            notifyBtn.style.display = 'none';
            doneBtn.style.display   = 'none';
          }

          // Next card…
          const nextCard = document.getElementById('call-next-card');
          const nextName = nextCard.querySelector('.next-name');
          const nextNo   = nextCard.querySelector('.next-studentno');
          const callBtn  = document.getElementById('call-btn');
          if (data.next) {
            nextName.textContent = data.next.firstname + ' ' + data.next.lastname;
            nextName.classList.remove('text-muted');
            nextNo.textContent   = data.next.studentno;
            callBtn.style.display = 'inline-block';
            callBtn.dataset.studentno = data.next.studentno;
          } else {
            nextName.textContent = 'No one waiting';
            nextName.classList.add('text-muted');
            nextNo.textContent = '';
            callBtn.style.display = 'none';
          }

          if (data.next) {
            // existing code to populate nextName/nextNo…
            callBtn.style.display = 'inline-block';
            callBtn.dataset.studentno = data.next.studentno;

            // **DISABLE if someone is already being served**
            callBtn.disabled = !!data.serving;
          } else {
            // existing “no one waiting” code…
            callBtn.style.display = 'none';
          }
        })
        .catch(console.error);
    }

    // Call & Process
    document.getElementById('call-btn').addEventListener('click', () => {
      const no = document.getElementById('call-btn').dataset.studentno;
      axios.post('queue_action.php', new URLSearchParams({ studentno: no }))
        .then(() => {
          // After updating server, immediately re-poll
          setTimeout(refreshDashboard, 100);
        })
        .catch(() => alert('Failed to call next.'));
    });

    // Notify with 3s re-enable
    document.getElementById('notify-btn').addEventListener('click', () => {
      const btn = document.getElementById('notify-btn');
      const no  = btn.dataset.studentno;
      axios.post('notify.php', new URLSearchParams({ studentno: no }))
        .then(() => {
          btn.textContent = 'Notified';
          btn.disabled = true;
          // Re-enable after 3s
          setTimeout(() => {
            btn.disabled = false;
            btn.textContent = 'Notify';
          }, 3000);
        })
        .catch(() => alert('Failed to notify.'));
    });

    // Done action
    document.getElementById('done-btn').addEventListener('click', () => {
      const btn = document.getElementById('done-btn');
      const no  = btn.dataset.studentno;
      axios.post('done_action.php', new URLSearchParams({ studentno: no }))
        .then(({ data }) => {
          if (data.ok) {
            // after marking done, refresh to move them out of Now Serving
            setTimeout(refreshDashboard, 100);
          } else {
            alert('Failed to mark done.');
          }
        })
        .catch(() => alert('Failed to mark done.'));
    });

    // init
    refreshDashboard();
    setInterval(refreshDashboard, 5000);
  </script>
</body>
</html>
