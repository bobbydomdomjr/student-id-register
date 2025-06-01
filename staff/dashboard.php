<?php
// staff/dashboard.php
session_start();
require_once('../db.php');
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    header('Location: ./login/index.php');
    exit;
}
// Welcome message (once after login)
$welcome_message = $_SESSION['welcome_message'] ?? '';
unset($_SESSION['welcome_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="../dist/axios/axios.min.js"></script>
   <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons & Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <style>
    body {  
  display: flex;
  min-height: 100vh;
  margin: 0; /* Remove default margin */
  overflow-x: hidden; /* Prevent horizontal scroll */
}

#sidebar {
  width: 240px;
  background: #343a40;
  color: #fff;
  flex-shrink: 0; /* Prevent shrinking */
  height: 100vh; /* Full height */
  position: fixed; /* Fix sidebar position */
  top: 0;
  left: 0;
  overflow-y: auto; /* Scroll if content overflows */
}

#content {
  margin-left: 240px; /* Offset content to the right of sidebar */
  flex: 1;
  padding: 20px;
  background: #f4f6f9;
  min-height: 100vh;
  overflow-y: auto;
}
  </style>
</head>
<body>

  <?php include __DIR__ . '/../includes/sidebar1.php'; ?>
  <!-- Sidebar unchanged -->

<div id="content">
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


  <!-- Floating Chat Button -->
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Chat Button -->
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
function confirmLogout() {
  return confirm("Are you sure you want to logout?");
}
</script>
<!-- Bootstrap 5 (make sure this is in your HTML before the closing </body> tag) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>

</body>
</html>
