<?php
// admin/ad_manager.php
require_once __DIR__ . '/../includes/init.php';

// File size limit
$maxFileSize = 50 * 1024 * 1024;

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $f = $_FILES['ad_file'];
    $start = date('Y-m-d H:i:s', strtotime($_POST['start_time']));
    $end   = date('Y-m-d H:i:s', strtotime($_POST['end_time']));

    if ($f['error'] === 0 && $f['size'] <= $maxFileSize) {
        $target = 'ads/' . time() . '_' . basename($f['name']);
        if (move_uploaded_file($f['tmp_name'], $target)) {
            $stmt = $conn->prepare("INSERT INTO ads(filename,start_time,end_time) VALUES(?,?,?)");
            $stmt->bind_param('sss', $target, $start, $end);
            $stmt->execute();
        }
    }
    header('Location: ad_manager.php');
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $r = $conn->query("SELECT filename FROM ads WHERE id=$id");
    if ($row = $r->fetch_assoc()) unlink($row['filename']);
    $conn->query("DELETE FROM ads WHERE id=$id");
    header('Location: ad_manager.php');
    exit();
}

// Load ads
$ads = $conn->query("
  SELECT id, filename,
    DATE_FORMAT(start_time,'%Y-%m-%d %h:%i %p') AS ds,
    DATE_FORMAT(end_time,  '%Y-%m-%d %h:%i %p') AS de,
    uploaded_at
  FROM ads
  ORDER BY uploaded_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Advertisement Management</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        body { margin:0; }
        .home-section {
            margin-left:250px; padding:2rem;
            background:#f8f9fa; min-height:100vh;
            transition: margin-left .3s;
        }
        .sidebar.open ~ .home-section { margin-left:80px; }

        @media (max-width:768px) {
            .sidebar { width:0!important; }
            .sidebar.open { width:250px!important; }
            .home-section { margin-left:0!important; }
        }

        .home-section .text { font-size:1.75rem; font-weight:600; color:#343a40; }
        .table img, .table video { max-width:120px; height:auto; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<section class="home-section">
    <div class="text">Advertisement Management</div>
    <hr>

    <div class="container-fluid">
        <form method="POST" enctype="multipart/form-data" class="row g-3 mb-5">
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
            <div class="col-md-2 d-grid">
                <button name="upload" class="btn btn-primary">Upload</button>
            </div>
        </form>

        <h5 class="mb-3">Uploaded Advertisements</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
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
                    <tr class="text-center">
                        <td>
                            <?php $ext = pathinfo($ad['filename'], PATHINFO_EXTENSION); ?>
                            <?php if (in_array(strtolower($ext), ['mp4','avi','mov','mkv'])): ?>
                                <video controls><source src="<?= $ad['filename'] ?>"></video>
                            <?php else: ?>
                                <img src="<?= $ad['filename'] ?>">
                            <?php endif; ?>
                        </td>
                        <td><?= $ad['ds'] ?></td>
                        <td><?= $ad['de'] ?></td>
                        <td><?= $ad['uploaded_at'] ?></td>
                        <td>
                            <a href="?delete=<?= $ad['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this ad?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
