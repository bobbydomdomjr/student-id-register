<?php
// admin/queue_manager.php
require_once __DIR__ . '/../includes/init.php';

// Pagination & search
$limit      = 10;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $limit;
$searchTerm = '%' . ($conn->real_escape_string(trim($_GET['search'] ?? ''))) . '%';

// Fetch queue entries
$stmt = $conn->prepare("
    SELECT id, studentno, firstname, middleinitial, lastname,
           course, yearlevel, block, email, registration_date, status
      FROM student_registration
     WHERE CONCAT(firstname,' ',lastname,' ',course) LIKE ?
     ORDER BY registration_date ASC
     LIMIT ? OFFSET ?
");
$stmt->bind_param('sii', $searchTerm, $limit, $offset);
$stmt->execute();
$queueResult = $stmt->get_result();

// Count total
$stmt2 = $conn->prepare("
    SELECT COUNT(*) FROM student_registration
     WHERE CONCAT(firstname,' ',lastname,' ',course) LIKE ?
");
$stmt2->bind_param('s', $searchTerm);
$stmt2->execute();
$totalRows = $stmt2->get_result()->fetch_row()[0];
$totalPages = (int)ceil($totalRows / $limit);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Queue Management · Admin</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { margin:0; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; }
        .home-section {
            margin-left:250px; padding:2rem;
            background:#f8f9fa; min-height:100vh;
            transition: margin-left .3s;
        }
        .sidebar.collapsed ~ .home-section { margin-left:80px; }
        @media (max-width:768px) {
            .sidebar { width:0!important; }
            .sidebar.collapsed { width:250px!important; }
            .home-section { margin-left:0!important; }
        }
        .home-section .text { font-size:1.75rem; font-weight:600; color:#343a40; }
        .status-dropdown, .status-badge { min-width:90px; }
        .custom-toggle-switch { display:flex; align-items:center; gap:1rem; margin:1rem 0; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<section class="home-section">
    <div class="text">Queue Management</div>
    <hr>

    <!-- Search + Pagination Controls -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="d-flex" method="GET">
            <input name="search" class="form-control form-control-sm me-2" placeholder="Search…" value="<?= htmlspecialchars(trim($_GET['search'] ?? '')) ?>">
            <button class="btn btn-outline-secondary btn-sm">Search</button>
        </form>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page<=1?'disabled':'' ?>">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode(trim($_GET['search'] ?? '')) ?>">Prev</a>
                </li>
                <?php for($i=1;$i<=$totalPages;$i++): ?>
                    <li class="page-item <?= $i===$page?'active':'' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode(trim($_GET['search'] ?? '')) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode(trim($_GET['search'] ?? '')) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Queuing Enabled Toggle -->
    <?php
    $cfg = $conn->query("SELECT queuing_enabled FROM settings WHERE id=1")->fetch_assoc();
    $enabled = $cfg['queuing_enabled'] ? 'checked' : '';
    ?>
    <form method="POST" action="toggle_queuing_system.php" class="custom-toggle-switch">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="queuingSwitch" name="toggle_queuing_system" <?= $enabled ?> onchange="this.form.submit()">
            <label class="form-check-label" for="queuingSwitch">Queuing <?= $enabled?'Enabled':'Disabled' ?></label>
        </div>
    </form>

    <!-- Queue Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm align-middle">
            <thead class="table-light text-center">
            <tr>
                <th>No.</th><th>Student</th><th>Course/Year/Block</th>
                <th>Email</th><th>Registered</th><th>Status</th>
            </tr>
            </thead>
            <?php
            $colorMap = [
                'pending'    => 'warning',
                'processing' => 'info',
                'done'       => 'success'
            ];
            $labelMap = [
                'pending'    => 'Waiting',
                'processing' => 'Processing',
                'done'       => 'Completed'
            ];
            ?>
            <tbody>
            <?php while($r = $queueResult->fetch_assoc()):
                // internal code
                $raw = strtolower($r['status']);
                // badge color
                $cls   = $colorMap[$raw] ?? 'secondary';
                ?>
                <tr class="text-center">
                    <td><?= htmlspecialchars($r['studentno']) ?></td>
                    <td><?= htmlspecialchars("{$r['firstname']} {$r['middleinitial']} {$r['lastname']}") ?></td>
                    <td><?= htmlspecialchars("{$r['course']} {$r['yearlevel']} {$r['block']}") ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars($r['registration_date']) ?></td>
                    <td>
                        <select
                                class="form-select form-select-sm status-dropdown d-inline-block w-auto"
                                data-id="<?= $r['id'] ?>">
                            <?php foreach (['pending','processing','done'] as $opt): ?>
                                <option
                                        value="<?= $opt ?>"
                                    <?= $opt === $raw ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($labelMap[$opt]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Bootstrap 5 Bundle + jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // status update via AJAX
    $(function(){
        $('.status-dropdown').on('change', function(){
            let id = $(this).data('id'), status = $(this).val();
            $.post('update_status.php',{id,status}, function(res){
                let r = JSON.parse(res);
                let iconMap = {pending:'⏳', processing:'🔄', done:'✅'};
                if(r.success){
                    let badge = iconMap[status] + ' ' + status;
                    showToast(iconMap[status], 'Status set to '+status,true);
                } else showToast('❌','Update failed',false);
            }).fail(()=>showToast('❌','Server error',false));
        });
    });
    // toast
    function showToast(title,msg,ok){
        const toast = $(`
        <div class="toast align-items-center text-bg-${ok?'success':'danger'} border-0" role="alert" data-bs-delay="3000">
          <div class="d-flex">
            <div class="toast-body"><strong>${title}</strong> ${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>`);
        $('.home-section').append(toast);
        toast.toast('show').on('hidden.bs.toast',()=>toast.remove());
    }
</script>
</body>
</html>
