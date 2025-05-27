<?php
// staff/picture_upload.php
session_start();
require_once('../db.php');

// Only staff
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    header("Location: ./login/index.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (empty($_POST['studentno']) || !isset($_FILES['photo'])) {
        $errors[] = "Student No. and photo file are required.";
    } else {
        $no = $_POST['studentno'];
        $file = $_FILES['photo'];
        // Basic validation
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png'];
        if (!in_array(strtolower($ext), $allowed)) {
            $errors[] = "Invalid file type.";
        } elseif ($file['error']!==0) {
            $errors[] = "Upload error.";
        } else {
            // Move file
            $dest = "../uploads/{$no}." . $ext;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = "Failed to save file.";
            } else {
                // Update DB
                $stmt = $conn->prepare("UPDATE student_registration SET picture = ? WHERE studentno = ?");
                $stmt->bind_param("ss", $dest, $no);
                $stmt->execute();
                $stmt->close();
                header("Location: picture_upload.php?success=1");
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Picture Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h3>Upload / Update Student Photo</h3>
  <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul>
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul></div>
  <?php elseif (isset($_GET['success'])): ?>
    <div class="alert alert-success">Photo uploaded successfully!</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Student No.</label>
      <input type="text" name="studentno" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Photo File</label>
      <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png" required>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Upload Photo</button>
    </div>
  </form>
</body>
</html>
