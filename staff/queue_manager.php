<?php
// staff/queue_manager.php
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
  <title>Queue Manager</title>
  <meta name="viewport" content="width=device-width,initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../dist/axios/axios.min.js"></script>
  <style>
    /* Highlight for the “Now Serving” card */
    .card-now {
      border-left: .25rem solid #28a745;
      background-color: #f8fdf8;
    }
    /* Ensure horizontal scroll on tiny viewports */
    .table-responsive {
      overflow-x: auto;
    }
  </style>
</head>
<body class="bg-light">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">Admin Panel</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
              data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">← Dashboard</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <h1 class="h3 mb-4 text-secondary">Queue Management</h1>

    <!-- Now Serving -->
    <div class="card mb-4 card-now shadow-sm">
      <div class="card-header bg-white">
        <span class="fw-bold">Now Serving</span>
      </div>
      <div class="card-body" id="nowServing">
        <p class="text-muted mb-0">Loading…</p>
      </div>
    </div>

    <!-- Status Cards Grid -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
      <!-- Processing -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0">Processing</h6>
          </div>
          <div class="card-body p-2 table-responsive">
            <table class="table table-sm mb-0">
              <thead class="visually-hidden">
                <tr><th>Name</th><th>Student No</th><th>Actions</th></tr>
              </thead>
              <tbody id="processingBody">
                <tr><td colspan="3" class="text-muted text-center py-3">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pending -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">Pending</h6>
          </div>
          <div class="card-body p-2 table-responsive">
            <table class="table table-sm mb-0">
              <thead class="visually-hidden">
                <tr><th>Name</th><th>Student No</th><th>Actions</th></tr>
              </thead>
              <tbody id="pendingBody">
                <tr><td colspan="3" class="text-muted text-center py-3">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No‑Show -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-secondary text-white">
            <h6 class="mb-0">No‑Show</h6>
          </div>
          <div class="card-body p-2 table-responsive">
            <table class="table table-sm mb-0">
              <thead class="visually-hidden">
                <tr><th>Name</th><th>Student No</th><th>Actions</th></tr>
              </thead>
              <tbody id="noShowBody">
                <tr><td colspan="3" class="text-muted text-center py-3">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Done -->
      <div class="col">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">Done</h6>
          </div>
          <div class="card-body p-2 table-responsive">
            <table class="table table-sm mb-0">
              <thead class="visually-hidden">
                <tr><th>Name</th><th>Student No</th><th>Actions</th></tr>
              </thead>
              <tbody id="doneBody">
                <tr><td colspan="3" class="text-muted text-center py-3">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="./../config/global.js"></script>
    <script>
        const nowEl      = document.getElementById('nowServing');
        const procBody   = document.getElementById('processingBody');
        const pendBody   = document.getElementById('pendingBody');
        const noShowBody = document.getElementById('noShowBody');
        const doneBody   = document.getElementById('doneBody');

        // flag for whether any student is currently processing
        let isProcessing = false;

        function refreshAll() {
            axios.get(`${appUrl()}/admin/fetch_queue.php`)
            .then(({data}) => {
                renderNow(data.serving);
                // first load processing so we know if there's someone processing
                loadList('processing', procBody)
                .then(() => {
                    // after processing list is rendered, load pending with correct button state
                    loadList('pending', pendBody);
                });
                loadList('no-show', noShowBody);
                loadList('done', doneBody);
            })
            .catch(err => console.error(err));
        }

        function renderNow(s) {
            nowEl.innerHTML = '';
            if (!s) {
            nowEl.innerHTML = '<p class="text-muted mb-0">None</p>';
            return;
            }
            nowEl.innerHTML = `
            <h5 class="fw-bold mb-1">${s.firstname} ${s.lastname}</h5>
            <p class="text-muted mb-2 small">${s.studentno}</p>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-success flex-fill" onclick="upd('${s.studentno}','done')">Done</button>
                <button class="btn btn-sm btn-warning flex-fill" onclick="upd('${s.studentno}','no-show')">No‑Show</button>
                <button class="btn btn-sm btn-secondary flex-fill" onclick="upd('${s.studentno}','pending')">Re‑Pend</button>
            </div>`;
        }

        /**
         * Fetches and renders a list of students by status,
         * and toggles all ".pending-btn" buttons based on isProcessing.
         *
         * @param {string} status  One of 'processing', 'pending', 'no-show', 'done'
         * @param {HTMLElement} tbody  The <tbody> element to populate
         * @returns {Promise<void>}
         */
        function loadList(status, tbody) {
        return axios.get(`${appUrl()}/staff/api/fetch_by_status.php?status=${status}`)
            .then(({ data }) => {
            // Clear existing rows
            tbody.innerHTML = '';

            // If no entries, show placeholder
            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center py-2">None</td></tr>';
                if (status === 'processing') {
                isProcessing = false;
                // Re-enable all pending/no-show Process buttons
                togglePendingButtons(false);
                }
                return;
            }

            // Mark that someone is processing if status === 'processing'
            if (status === 'processing') {
                isProcessing = true;
            }

            // Render each student row
            data.forEach(u => {
                const name = `${u.firstname} ${u.lastname}`;
                const no   = u.studentno;
                let btns = '';

                switch (status) {
                case 'pending':
                    btns = `<button class="btn btn-sm btn-outline-primary pending-btn"
                                    onclick="upd('${no}','processing')"
                                    ${isProcessing ? 'disabled' : ''}>
                            Process
                            </button>`;
                    break;

                case 'processing':
                    btns = `
                    <button class="btn btn-sm btn-outline-success" onclick="upd('${no}','done')">Done</button>
                    <button class="btn btn-sm btn-outline-warning" onclick="upd('${no}','no-show')">No-Show</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="upd('${no}','pending')">Re-Pend</button>`;
                    break;

                case 'no-show':
                    btns = `
                    <button class="btn btn-sm btn-outline-secondary" onclick="upd('${no}','pending')">Re-Pend</button>
                    <button class="btn btn-sm btn-outline-primary pending-btn"
                            onclick="upd('${no}','processing')"
                            ${isProcessing ? 'disabled' : ''}>
                        Process
                    </button>`;
                    break;

                case 'done':
                    btns = `<button class="btn btn-sm btn-outline-secondary" onclick="upd('${no}','pending')">Re-Pend</button>`;
                    break;
                }

                const row = `
                <tr>
                    <td class="align-middle small">${name}</td>
                    <td class="align-middle small">${no}</td>
                    <td class="align-middle small text-end">${btns}</td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });

            // After any of Processing, Pending, or No-Show loads,
            // disable/enable all pending-btns according to isProcessing
            if (['processing','pending','no-show'].includes(status)) {
                togglePendingButtons(isProcessing);
            }
            })
            .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="3" class="text-danger text-center py-2">Error loading</td></tr>';
            });
        }

        // enable/disable all pending “Process” buttons
        function togglePendingButtons(disabled) {
            document.querySelectorAll('.pending-btn')
            .forEach(btn => btn.disabled = disabled);
        }

        function upd(studentno, status) {
            axios.post(
            `${window.location.origin}/stu_reg/staff/api/update_status.php`,
            new URLSearchParams({ studentno, status })
            )
            .then(r => {
            if (r.data.ok) refreshAll();
            else alert('Update failed');
            })
            .catch(() => alert('Error'));
        }

        // initialize and auto-refresh every 10 seconds
        refreshAll();
        setInterval(refreshAll, 10000);
    </script>

  <!-- Bootstrap JS bundle (Popper + JS) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
