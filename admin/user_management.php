<?php
// admin/user_management.php
require_once __DIR__ . '/../includes/init.php';

// Load all admin/staff (exclude superadmin)
$sql = "SELECT id, username, role
        FROM admin
        WHERE role IN ('admin','staff')
        ORDER BY username";
$stmt = $conn->query($sql);
$accounts = $stmt->fetch_all(MYSQLI_ASSOC);

// Feedback messages
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { margin:0; }
        .home-section { margin-left:250px; padding:2rem; background:#f8f9fa; min-height:100vh; transition: margin-left .3s; }
        .sidebar.open ~ .home-section { margin-left:80px; }
        @media (max-width:768px) { .sidebar {width:0!important;} .sidebar.open {width:250px;} .home-section {margin-left:0;} }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<section class="home-section p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-user-plus"></i> Add Account
        </button>
    </div>

    <?php if($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <input id="searchInput" type="text" class="form-control" placeholder="Search username…">
    </div>

    <div class="row g-3" id="accountsContainer">
        <?php foreach($accounts as $a): ?>
            <div class="col-md-4 account-card" data-username="<?= strtolower($a['username']) ?>">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><?= htmlspecialchars($a['username']) ?></h5>
                            <small class="text-muted"><?= ucfirst($a['role']) ?></small>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-warning editBtn"
                                    data-id="<?= $a['id'] ?>" data-bs-toggle="modal" data-bs-target="#editModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary resetBtn"
                                    data-id="<?= $a['id'] ?>">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger deleteBtn"
                                    data-id="<?= $a['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="add_account.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Username</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input name="password" type="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="edit_account.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label>Username</label>
                    <input name="username" id="edit-username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>New Password <small>(leave blank to keep)</small></label>
                    <input name="password" type="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" id="edit-role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Client-side search
    document.getElementById('searchInput').addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.account-card').forEach(card => {
            card.style.display = card.dataset.username.includes(q) ? '' : 'none';
        });
    });

    // Edit button → fetch JSON
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.onclick = () => {
            fetch(`edit_account.php?id=${btn.dataset.id}`)
                .then(r => r.json())
                .then(u => {
                    document.getElementById('edit-id').value = u.id;
                    document.getElementById('edit-username').value = u.username;
                    document.getElementById('edit-role').value = u.role;
                });
        };
    });

    // Delete & Reset (simple confirm + redirect)
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.onclick = () => {
            if (confirm('Delete this account?')) {
                window.location = `delete_account.php?id=${btn.dataset.id}`;
            }
        };
    });
    document.querySelectorAll('.resetBtn').forEach(btn => {
        btn.onclick = () => {
            if (confirm('Reset password to role + "123"?')) {
                window.location = `reset_password.php?id=${btn.dataset.id}`;
            }
        };
    });
</script>
</body>
</html>
