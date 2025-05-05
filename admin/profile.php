<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch Admin Profile Data
$admin_id = $_SESSION['admin']['id']; // Assuming the admin's ID is stored in the session
$sql = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Update Profile Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // If password is provided, update it
        $password = password_hash($password, PASSWORD_BCRYPT);
        $update_sql = "UPDATE admin SET name = ?, email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssi", $name, $email, $password, $admin_id);
    } else {
        // If no new password is provided, just update the name and email
        $update_sql = "UPDATE admin SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssi", $name, $email, $admin_id);
    }

    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Failed to update profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <button id="sidebarToggle" class="btn"><i class="fas fa-bars"></i></button>
    <h4 class="text-center py-3">Admin Panel</h4>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
    <a href="students.php"><i class="fas fa-user-graduate"></i> <span>Students</span></a>
    <a href="reports.php"><i class="fas fa-chart-bar"></i> <span>Reports</span></a>
    <a href="profile.php" class="active"><i class="fas fa-user"></i> <span>Profile</span></a>
    <a href="logout.php" onclick="return confirmLogout()" class="logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
</div>

<!-- Content Area -->
<div id="content">
    <div class="container-fluid">
        <h2>Admin Profile</h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Form -->
        <form method="POST" action="profile.php">
            <div class="form-row mb-4">
                <div class="col-md-6">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
            </div>

            <div class="form-row mb-4">
                <div class="col-md-6">
                    <label for="password">New Password (leave empty if no change)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="col-md-6">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<script>
    // Logout Confirmation
    function confirmLogout() {
        return confirm('Are you sure you want to logout?');
    }
</script>

</body>
</html>
