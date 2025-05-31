<?php
require_once __DIR__ . '/../includes/init.php';

// Pagination & search setup
$limit       = 10;
$page        = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($page - 1) * $limit;
$search_raw  = trim($_GET['search'] ?? '');
$search_like = '%' . $search_raw . '%';

// Prepare student list query
$stmt = $conn->prepare("
    SELECT id, studentno, firstname, middleinitial, lastname, registration_date, status, picture
      FROM student_registration
     WHERE CONCAT(firstname,' ',lastname,' ',course) LIKE ?
     ORDER BY registration_date DESC
     LIMIT ? OFFSET ?
");

$stmt->bind_param('sii', $search_like, $limit, $offset);
$stmt->execute();
$result_students = $stmt->get_result();

// Prepare total-count query
$stmt2 = $conn->prepare("
    SELECT COUNT(*) AS total
      FROM student_registration
     WHERE CONCAT(firstname,' ',lastname,' ',course) LIKE ?
");
$stmt2->bind_param('s', $search_like);
$stmt2->execute();
$total_students = $stmt2->get_result()->fetch_assoc()['total'];
$total_pages    = (int)ceil($total_students / $limit);

// Status → Frontend label map
$statusLabelMap = [
    'pending'    => 'Waiting',
    'processing' => 'Processing',
    'done'       => 'Completed',
    'no-show'    => 'No-Show'
];
// Display label → badge color map
$badgeColorMap = [
    'Waiting'     => 'warning',
    'Processing'  => 'success',
    'Completed'   => 'info',
    'No-Show'     => 'danger'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Students</title>
    <!-- Bootstrap 5 -->
    <link href="./../dist/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body, .home-section, .sidebar {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        }
        .home-section { padding:2rem; background:#f8f9fa; min-height:100vh; }
        .home-section .text { font-size:1.75rem; font-weight:600; margin-bottom:1rem; color:#343a40; }
        .card-students { border:none; border-radius:.75rem; box-shadow:0 .5rem 1rem rgba(0,0,0,.05); }
        .card-header { display:flex; justify-content:space-between; align-items:center; background:#fff; border-bottom:1px solid #e9ecef; }
        .controls { display:flex; gap:.5rem; }
        .controls .form-control { width:250px; }
        .table-sm { font-size:.875rem; }
        .table-hover tbody tr:hover { background:rgba(0,0,0,.03); }
        .pagination .page-link { border-radius:.375rem; padding:.375rem .75rem; font-size:.875rem; }
        .home-section {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        .sidebar.open ~ .home-section {
            margin-left: 80px;
        }
        @media (max-width: 768px) {
            .sidebar { position: absolute; width: 0; transition: width 0.3s ease; }
            .sidebar.open { width: 250px; }
            .home-section { margin-left: 0; }
        }
   .img-wrapper {
    position: relative;
    display: inline-block;
  }

  .img-thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
    border: 1px solid #ccc;
    cursor: pointer;
  }

  .img-preview {
  display: none;
  position: absolute;
  top: 50%;
  left: 110%; /* a bit further right from thumbnail */
  transform: translateY(-50%) scale(0.9);
  width: 200px;
  max-height: 200px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
  border: 1px solid #ccc;
  background: white;
  z-index: 100;
  
  opacity: 0;
  transition: opacity 0.3s ease, transform 0.3s ease;
  pointer-events: none; /* so it doesn't interfere with mouse */
}

/* Show and animate preview on hover */
.img-wrapper:hover .img-preview {
  display: block;
  opacity: 1;
  transform: translateY(-50%) scale(1);
  pointer-events: auto;
}

/* Optional arrow pointing to the thumbnail */
.img-preview::before {
  content: "";
  position: absolute;
  top: 50%;
  left: -8px;
  transform: translateY(-50%);
  border: 8px solid transparent;
  border-right-color: white;
  filter: drop-shadow(-1px 0 1px rgba(0,0,0,0.1));
}


  .img-wrapper:hover .img-preview {
    display: block;
  }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="home-section">
    <div class="text">Registered Students</div>
    <hr>

    <div class="card card-students">
        <div class="card-header">
            <h5 class="mb-0">Student List</h5>
            <div class="controls">
                <div class="dropdown">
                    <button class="btn btn-outline-success btn-sm dropdown-toggle"
                            id="exportDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="export_csv.php"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                        <li><a class="dropdown-item" href="export_pdf.php"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                    </ul>
                </div>
                <form method="GET" class="d-flex">
                    <input type="hidden" name="page" value="1">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search students…" value="<?= htmlspecialchars($search_raw) ?>">
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <table class="table table-striped table-hover table-sm mb-0">
                <thead class="table-light text-center">
                <tr>
                    <th>Photo</th>
                    <th>Student No.</th>
                    <th>Full Name</th>
                    <th>Registered Date &amp; Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($s = $result_students->fetch_assoc()): ?>
                    <?php
                    // Map raw status to display label
                    $raw = strtolower($s['status']);
                    $disp = $statusLabelMap[$raw] ?? ucfirst($raw);
                    $clr  = $badgeColorMap[$disp] ?? 'secondary';
                    ?>
                    <tr>
<td class="text-center align-middle">
  <?php if (!empty($s['picture']) && file_exists($s['picture'])): ?>
    <div class="img-wrapper">
      <img src="<?= htmlspecialchars($s['picture']) ?>" alt="Photo" class="img-thumb">
      <img src="<?= htmlspecialchars($s['picture']) ?>" alt="Preview" class="img-preview">
    </div>
  <?php else: ?>
    <span class="text-muted"><i class="fas fa-user-circle fa-2x"></i></span>
  <?php endif; ?>
</td>

                        <td class="text-center align-middle"><?= htmlspecialchars($s['studentno']) ?></td>
                        <td class="align-middle">
                            <?= htmlspecialchars("{$s['firstname']} {$s['middleinitial']} {$s['lastname']}") ?>
                        </td>
                        <td class="text-center align-middle"><?= htmlspecialchars($s['registration_date']) ?></td>
                        <td class="text-center align-middle">
                            <span class="badge bg-<?= $clr ?>"><?= $disp ?></span>
                        </td>
                        <td class="text-center align-middle">
                            <button class="btn btn-outline-info btn-sm view-btn" data-id="<?= $s['id'] ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-warning btn-sm edit-btn"
        data-id="<?= $s['id'] ?>">
    <i class="fas fa-edit"></i>
</button>

                            <a href="delete_student.php?id=<?= $s['id'] ?>"
                               onclick="return confirm('Are you sure?')" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $page<=1 ? 'disabled':'' ?>">
                        <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search_raw) ?>">Previous</a>
                    </li>
                    <?php for ($i=1; $i<=$total_pages; $i++): ?>
                        <li class="page-item <?= $i===$page ? 'active':'' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_raw) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page>=$total_pages ? 'disabled':'' ?>">
                        <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search_raw) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewStudentModal" tabindex="-1" aria-labelledby="previewStudentLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow rounded">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="previewStudentLabel">Student Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex gap-4">
        <img id="preview-photo" src="" alt="Student Photo" class="rounded border" style="width: 180px; height: 180px; object-fit: cover;">
        <div>
          <h5 id="preview-name" class="mb-2"></h5>
          <p class="mb-1"><strong>Student No:</strong> <span id="preview-studentno"></span></p>
          <p class="mb-1"><strong>Status:</strong> <span id="preview-status" class="badge"></span></p>
          <p class="mb-0"><strong>Registered:</strong> <span id="preview-date"></span></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editStudentForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editStudentLabel">Edit Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <div class="mb-3">
            <label for="edit-studentno" class="form-label">Student No.</label>
            <input type="text" class="form-control" name="studentno" id="edit-studentno" required>
          </div>
          <div class="mb-3">
            <label for="edit-firstname" class="form-label">First Name</label>
            <input type="text" class="form-control" name="firstname" id="edit-firstname" required>
          </div>
          <div class="mb-3">
            <label for="edit-middleinitial" class="form-label">Middle Initial</label>
            <input type="text" class="form-control" name="middleinitial" id="edit-middleinitial">
          </div>
          <div class="mb-3">
            <label for="edit-lastname" class="form-label">Last Name</label>
            <input type="text" class="form-control" name="lastname" id="edit-lastname" required>
          </div>
          <div class="mb-3">
            <label for="edit-status" class="form-label">Status</label>
            <select class="form-select" name="status" id="edit-status">
              <option value="pending">Waiting</option>
              <option value="processing">Processing</option>
              <option value="done">Completed</option>
              <option value="no-show">No-Show</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>  

<!-- Bootstrap 5 Bundle -->
<script src="./../dist/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
<script>
  function togglePreview(img) {
    const wrapper = img.parentElement;
    const preview = wrapper.querySelector('.img-preview');
    // Toggle preview display
    if (preview.style.display === 'block') {
      preview.style.display = 'none';
    } else {
      // Close any other open previews first
      document.querySelectorAll('.img-preview').forEach(p => p.style.display = 'none');
      preview.style.display = 'block';
    }
  }

  // Optional: click outside to close preview
  document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('img-thumb')) {
      document.querySelectorAll('.img-preview').forEach(p => p.style.display = 'none');
    }
  });
</script>
<script>
  // Optional: clean up leftover backdrops if modals glitch
  document.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
  });
</script>

<script>
  const editModal = document.getElementById('editStudentModal');

  editModal.addEventListener('hidden.bs.modal', function () {
    // Reset form on close (optional, if you want to clear it)
    document.getElementById('editStudentForm').reset();
  });

  // Optional: Ensure modal opens with populated values if you're editing
  function openEditModal(student) {
    document.getElementById('edit-id').value = student.id;
    document.getElementById('edit-studentno').value = student.studentno;
    document.getElementById('edit-firstname').value = student.firstname;
    document.getElementById('edit-middleinitial').value = student.middleinitial;
    document.getElementById('edit-lastname').value = student.lastname;
    document.getElementById('edit-status').value = student.status;

    const modal = new bootstrap.Modal(editModal);
    modal.show();
  }
</script>

<script>
  document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const studentId = btn.dataset.id;
      
      fetch(`get_student.php?id=${studentId}`)
        .then(res => res.json())
        .then(data => {
          document.getElementById('edit-id').value = data.id;
          document.getElementById('edit-studentno').value = data.studentno;
          document.getElementById('edit-firstname').value = data.firstname;
          document.getElementById('edit-middleinitial').value = data.middleinitial;
          document.getElementById('edit-lastname').value = data.lastname;
          document.getElementById('edit-status').value = data.status;

          const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
          modal.show();
        });
    });
  });

  document.getElementById('editStudentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('update_student.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(response => {
      alert('Student updated successfully!');
      location.reload(); // Or update the row without reloading
    })
    .catch(err => {
      alert('Failed to update student.');
      location.reload(); // Or update the row without reloading
    });
  });
</script>
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const studentId = btn.dataset.id;

    fetch(`get_student.php?id=${studentId}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('edit-id').value = data.id;
        document.getElementById('edit-studentno').value = data.studentno;
        document.getElementById('edit-firstname').value = data.firstname;
        document.getElementById('edit-middleinitial').value = data.middleinitial || '';
        document.getElementById('edit-lastname').value = data.lastname;
        document.getElementById('edit-status').value = data.status;

        const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        modal.show();
      })
      .catch(err => alert('Failed to fetch student details.'));

  });
});

// Handle form submission
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('update_student.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(response => {
    if (response.success) {
      alert('Student updated successfully!');
      location.reload(); // Or dynamically update the row
    } else {
      alert('Update failed: ' + (response.message || 'Unknown error'));
    }
  })
  .catch(err => alert('An error occurred while updating.'));
});
</script>
<script>
document.querySelectorAll('.view-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    fetch(`get_student.php?id=${id}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('preview-photo').src = data.picture || 'default.jpg';
        document.getElementById('preview-name').textContent = `${data.firstname} ${data.middleinitial} ${data.lastname}`;
        document.getElementById('preview-studentno').textContent = data.studentno;
        document.getElementById('preview-date').textContent = data.registration_date;

        const statusMap = {
          pending: ['Waiting', 'bg-warning'],
          processing: ['Processing', 'bg-success'],
          done: ['Completed', 'bg-info'],
          'no-show': ['No-Show', 'bg-danger']
        };

        const [label, badge] = statusMap[data.status] || ['Unknown', 'bg-secondary'];
        const statusEl = document.getElementById('preview-status');
        statusEl.textContent = label;
        statusEl.className = `badge ${badge}`;

        new bootstrap.Modal(document.getElementById('previewStudentModal')).show();
      });
  });
});
</script>


</html>
