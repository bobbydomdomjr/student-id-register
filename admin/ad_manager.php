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

// Handle file upload
if (isset($_POST['upload'])) {
    $file = $_FILES['ad_file'];
    $start = $_POST['start_time']; // coming as YYYY-MM-DDTHH:MM
    $end = $_POST['end_time']; // coming as YYYY-MM-DDTHH:MM

    // Convert to a standard DATETIME format (YYYY-MM-DD HH:MM:SS)
    $start = date('Y-m-d H:i:s', strtotime($start));
    $end = date('Y-m-d H:i:s', strtotime($end));

    // Check for file upload error
    if ($file['error'] === 0) {
        // Check file size (should not exceed 50 MB)
        if ($file['size'] > $maxFileSize) {
            echo "File is too large. Max allowed size is 50 MB.";
            exit();
        }

        // Allow any file type (video, image, documents, etc.)
        $targetDir = "ads/";
        $filename = basename($file['name']);
        $targetFile = $targetDir . time() . "_" . $filename;

        // Move uploaded file to target directory
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Insert ad details into the database
            $stmt = $conn->prepare("INSERT INTO ads (filename, start_time, end_time) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $targetFile, $start, $end);

            if ($stmt->execute()) {
           
            } else {
                echo "Error inserting advertisement: " . $stmt->error;
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "File upload error.";
    }
}
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

$ads = $conn->query("SELECT *, 
                            DATE_FORMAT(start_time, '%Y-%m-%d %h:%i:%s %p') AS formatted_start, 
                            DATE_FORMAT(end_time, '%Y-%m-%d %h:%i:%s %p') AS formatted_end 
                     FROM ads 
                     ORDER BY uploaded_at DESC");

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
        <div class="text">Advertisement Management</div>
        <hr>


        <div class="container">
            <form method="POST" enctype="multipart/form-data" class="mb-5">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Upload Advertisement</label>
                        <input type="file" name="ad_file" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="datetime-local" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="datetime-local" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="upload" class="btn btn-primary w-100">Upload</button>
                    </div>
                </div>
            </form>

            <h5>Uploaded Advertisements</h5>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Preview</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ad = $ads->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (in_array(pathinfo($ad['filename'], PATHINFO_EXTENSION), ['mp4', 'avi', 'mov', 'mkv'])): ?>
                                <video width="120" controls>
                                    <source src="<?= $ad['filename'] ?>" type="video/<?= pathinfo($ad['filename'], PATHINFO_EXTENSION) ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <img src="<?= $ad['filename'] ?>" width="120">
                            <?php endif; ?>
                        </td>
                        <td><?= $ad['formatted_start'] ?></td>
                        <td><?= $ad['formatted_end'] ?></td>
                        <td><?= $ad['uploaded_at'] ?></td>
                        <td>
                            <a href="ad_manager.php?delete=<?= $ad['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this ad?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <footer class="text-center text-muted mt-auto">
                <small>&copy; 2025 Student Registration System</small>
            </footer>
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
    
</body>

</html>
