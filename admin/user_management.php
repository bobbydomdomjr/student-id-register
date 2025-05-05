<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set max file size limit (50 MB)
$maxFileSize = 50 * 1024 * 1024; // 50 MB





// Fetch admin and staff accounts
$sql = "SELECT * FROM admin WHERE role IN ('admin', 'staff')";
$result = $conn->query($sql);

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $result = $conn->query("SELECT filename FROM ads WHERE id=$id");
    if ($row = $result->fetch_assoc()) {
        unlink($row['filename']); // delete file from server
    }
    $conn->query("DELETE FROM ads WHERE id=$id");
    header("Location: ad_manager.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
body {
            display: flex;
            margin: 0;
        }

        #sidebar {
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            color: white;
        }

        #sidebar a {
            padding: 15px;
            text-decoration: none;
            color: white;
            display: block;
        }

        #sidebar a:hover,
        #sidebar a.active {
            background-color: #495057;
        }

        #content {
            flex-grow: 1;
            padding: 20px;
        }

        .navbar {
            background-color: #343a40;
        }

        footer {
            margin-top: 100px;
            font-size: 14px;
            color: #495057;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-details">
            <i class="bx bxl-angular icon"></i>
            <div class="logo_name">Admin Panel</div>
            <i class="bx bx-menu" id="btn"></i>
        </div>
        <ul class="nav-list">
            <li>
            <a href="admin_dashboard.php">
                    <i class="bx bx-home"></i>
                    <span class="links_name">Home</span>
                </a>
                <span class="tooltip">Home</span>
            </li>
            <li>
                <a href="user_management.php">
                    <i class="bx bx-user-plus"></i>
                    <span class="links_name">User Management</span>
                </a>
                <span class="tooltip">User Management</span>
            </li>
            <li>
                <a href="students.php">
                    <i class="bx bx-group"></i>
                    <span class="links_name">Student Management</span>
                </a>
                <span class="tooltip">Student Management</span>
            </li>
            <li>
                <a href="queue_manager.php">
                    <i class="bx bx-add-to-queue"></i>
                    <span class="links_name">Queue Management</span>
                </a>
                <span class="tooltip">Queue Management</span>
            </li>
            <li>
                <a href="ad_manager.php">
                    <i class="bx bx-play-circle"></i>
                    <span class="links_name">Ads Management</span>
                </a>
                <span class="tooltip">Ads Management</span>
            </li>
            <li>
                <a href="#">
                    <i class="bx bx-file"></i>
                    <span class="links_name">Reports & Logs</span>
                </a>
                <span class="tooltip">Reports & Logs</span>
            </li>
            <li>
                <a href="logout.php" onclick="return confirmLogout()">
                    <i class="bx bx-log-out"></i>
                    <span class="links_name">Logout</span>
                </a>
                <span class="tooltip">Logout</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="home-section">
        <div class="text">User Management</div>
        <hr>
        
        <div class="container-fluid">
        <div class="container mt-5">
        <?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success'] ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $_SESSION['error'] ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Admin & Staff Accounts</h3>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addAccountModal">Add Account</button>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="card account-card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-1"><?= htmlspecialchars($row['username']) ?></h5>
            <p class="mb-0 text-muted">Role: <strong><?= ucfirst($row['role']) ?></strong></p>
          </div>
          <div>
          <button 
  class="btn btn-sm btn-warning edit-btn" 
  data-id="<?= $row['id'] ?>" 
  data-toggle="modal" 
  data-target="#editAccountModal">
  Edit
</button>

<a href="delete_account.php?id=<?= $row['id'] ?>" 
   class="btn btn-sm btn-danger"
   onclick="return confirm('Are you sure you want to delete this account? This action cannot be undone.')">
   Delete
</a>

          </div>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert alert-info">No admin or staff accounts found.</div>
  <?php endif; ?>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="add_account.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Account</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Username</label>
            <input name="username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control" required>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Add Account</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="edit_account.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Account</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <div class="form-group">
            <label>Username</label>
            <input name="username" id="edit-username" class="form-control" required>
          </div>
          <div class="form-group">
            <label>New Password <small>(Leave blank to keep current)</small></label>
            <input name="password" type="password" class="form-control">
          </div>
          <div class="form-group">
            <label>Role</label>
            <select name="role" id="edit-role" class="form-control" required>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success" type="submit">Update Account</button>
        </div>
      </div>
    </form>
  </div>
</div>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
 <script>
        let sidebar = document.querySelector(".sidebar");
        let closeBtn = document.querySelector("#btn");
        let searchBtn = document.querySelector(".bx-search");

        closeBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();
        });

        searchBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            menuBtnChange();
        });

        function menuBtnChange() {
            if (sidebar.classList.contains("open")) {
                closeBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                closeBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        }




        function confirmLogout() {
            return confirm('Are you sure you want to logout?');
        }
    </script>
    <script>
$(document).ready(function() {
  $('.edit-btn').click(function() {
    const id = $(this).data('id');
    $.ajax({
      url: 'edit_account.php',
      method: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function(data) {
        $('#edit-id').val(data.id);
        $('#edit-username').val(data.username);
        $('#edit-role').val(data.role);
      },
      error: function() {
        alert('Failed to fetch account data.');
      }
    });
  });
});
</script>

</body>

</html>
