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
    SELECT id, studentno, firstname, middleinitial, lastname, registration_date, status
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
        /* 1) Push content over by sidebar width */
        .home-section {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        /* 2) When sidebar is collapsed, shrink the margin */
        .sidebar.open ~ .home-section {
            margin-left: 80px;
        }

        /* 3) On small screens, hide sidebar by default and make content full width */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                width: 0;
                transition: width 0.3s ease;
            }
            .sidebar.open {
                width: 250px;
            }
            .home-section {
                margin-left: 0;
            }
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
                    <th>Student No.</th>
                    <th>Full Name</th>
                    <th>Registered Date &amp; Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($s = $result_students->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center align-middle"><?= htmlspecialchars($s['studentno']) ?></td>
                        <td class="align-middle">
                            <?= htmlspecialchars("{$s['firstname']} {$s['middleinitial']} {$s['lastname']}") ?>
                        </td>
                        <td class="text-center align-middle"><?= htmlspecialchars($s['registration_date']) ?></td>
                        <td class="text-center align-middle">
                            <?php
                            $st = ucfirst(strtolower($s['status']));
                            $map = ['Pending'=>'warning','Processing'=>'success','Done'=>'info'];
                            $clr = $map[$st] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $clr ?>"><?= $st ?></span>
                        </td>
                        <td class="text-center align-middle">
                            <button class="btn btn-outline-info btn-sm view-btn" data-id="<?= $s['id'] ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
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

<!-- Bootstrap 5 Bundle -->
<script src="./../dist/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Your existing JS for view-modal, notify, etc. -->
</body>
</html>
