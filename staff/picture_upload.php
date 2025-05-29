<?php
// staff/picture_upload.php
session_start();
require_once('../db.php');

// Staff authentication check
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ./login/index.php");
    exit();
}

// Fetch studentno, firstname, lastname from database
$students = [];
$result = $conn->query("SELECT studentno, firstname, lastname FROM student_registration ORDER BY studentno ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Configure upload directory
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['studentno']) || !isset($_FILES['photo'])) {
        $errors[] = "Student No. and photo file are required.";
    } else {
        $no = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['studentno']); // Sanitize student number
        $file = $_FILES['photo'];
        
        // File validation
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($mime, $allowed_mimes)) {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG allowed.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error: " . $file['error'];
        } else {
            // Generate safe filename
            $ext = str_replace('image/', '', $mime);
            $filename = "student_{$no}_" . time() . ".{$ext}";
            $dest = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Verify student exists before update
                $check_stmt = $conn->prepare("SELECT studentno FROM student_registration WHERE studentno = ?");
                $check_stmt->bind_param("s", $no);
                $check_stmt->execute();
                $check_stmt->store_result();
                
                if ($check_stmt->num_rows === 0) {
                    $errors[] = "Student number not found";
                    unlink($dest); // Remove uploaded file
                } else {
                    // Update database
                    $stmt = $conn->prepare("UPDATE student_registration SET picture = ? WHERE studentno = ?");
                    $stmt->bind_param("ss", $dest, $no);
                    if (!$stmt->execute()) {
                        $errors[] = "Database update failed: " . $stmt->error;
                        unlink($dest);
                    } else {
                        header("Location: picture_upload.php?success=1");
                        exit();
                    }
                }
                $check_stmt->close();
            } else {
                $errors[] = "Failed to save file. Check directory permissions.";
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

  <style>
 <style>
#cameraSection {
  border: 1px solid #ddd;
  padding: 20px;
  margin: 20px auto;
  border-radius: 10px;
  background: #fafafa;
  max-width: 500px;
}
.camera-ui video,
.camera-ui img {
  width: 100%;
  max-height: 360px;
  border-radius: 8px;
  background: #000;
}
</style>

  </style>
</head>
<body>
<div class="container py-4">
  <h3 class="mb-4 text-center">Upload Student Photo</h3>
  <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

 <!-- Toast Container (place once, e.g. after dashboard link) -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <?php if (!empty($errors)): ?>
      <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php elseif (isset($_GET['success'])): ?>
      <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            Photo uploaded successfully!
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>



<form method="POST" enctype="multipart/form-data" class="row g-3" onsubmit="return validateForm()">
  <!-- Header -->
  <div class="col-12 text-center mb-4">
  </div>

  <!-- Main Content Section: Left (Student Info), Right (Camera/Upload) -->
  <div class="col-md-6">
    <label for="studentno" class="form-label">Student No. & Name</label>
    <select name="studentno" id="studentno" class="form-select w-100" required>
      <option value="" disabled selected>Select Student</option>
      <?php foreach ($students as $student): ?>
        <option value="<?= htmlspecialchars($student['studentno']) ?>">
          <?= htmlspecialchars($student['studentno'] . ' - ' . $student['firstname'] . ' ' . $student['lastname']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label class="form-label mt-3">Upload Photo</label>
    <div id="dropZone" class="border border-secondary rounded p-3 text-center" style="cursor: pointer;">
  <p class="mb-0 text-muted">📤 Drag & Drop or Click to Upload</p>
  <input type="file" name="photo" accept=".jpg,.jpeg,.png" id="photoInput" class="d-none">
</div>

  </div>

  <div class="col-md-6">
    <div id="cameraSection">
      <label class="form-label">Or Use Camera:</label>
      <div class="camera-ui d-flex flex-column align-items-center gap-3 p-3 border rounded bg-light shadow-sm">
        <video id="video" autoplay playsinline class="w-100 rounded"></video>
        <img id="capturedPreview" alt="Captured preview" class="w-100 rounded border" style="display:none; object-fit: contain;" />
        <div class="d-flex justify-content-center gap-3 w-100">
          <button type="button" id="captureBtn" class="btn btn-primary flex-fill">
            <i class="bi bi-camera"></i> Capture Photo
          </button>
          <button type="button" id="recaptureBtn" class="btn btn-warning flex-fill" style="display:none;">
            <i class="bi bi-arrow-counterclockwise"></i> Recapture Photo
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom Button -->
  <div class="col-12 text-center mt-4">
    <button type="submit" class="btn btn-success btn-lg px-5">Submit Photo</button>
  </div>
</form>
<!-- Modal -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <img id="cropImage" class="img-fluid w-100"/>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="cropConfirm">Crop & Upload</button>
      </div>
    </div>
  </div>
</div>

</div>

<!-- jQuery (required by Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const video = document.getElementById('video');
const captureBtn = document.getElementById('captureBtn');
const recaptureBtn = document.getElementById('recaptureBtn');
const photoInput = document.getElementById('photoInput');
const capturedPreview = document.getElementById('capturedPreview');

let stream;

// Initialize camera stream
async function initCamera() {
  try {
    stream = await navigator.mediaDevices.getUserMedia({ 
      video: { facingMode: 'environment' }, 
      audio: false 
    });
    video.srcObject = stream;
    video.style.display = 'block';
    capturedPreview.style.display = 'none';
    captureBtn.style.display = 'inline-block';
    recaptureBtn.style.display = 'none';
    photoInput.value = ''; // Clear any previous file
  } catch (err) {
    console.error('Camera error:', err);
    document.getElementById('cameraSection').style.display = 'none';
  }
}

// Capture photo event
captureBtn.addEventListener('click', () => {
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);

  canvas.toBlob(blob => {
    const file = new File([blob], "captured_photo.png", { type: "image/png" });
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    photoInput.files = dataTransfer.files;

    // Show captured photo preview
    capturedPreview.src = URL.createObjectURL(blob);
    capturedPreview.style.display = 'block';

    // Hide video and capture button, show recapture button
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    recaptureBtn.style.display = 'inline-block';

    // Stop the camera stream to save resources
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }
  }, 'image/png');
});

// Recapture photo event
recaptureBtn.addEventListener('click', () => {
  initCamera();
});

// Initialize camera when page loads
window.addEventListener('load', initCamera);

// Initialize on select2 on your select
  $(document).ready(function() {
    $('#studentno').select2({
      placeholder: "Select Student",
      allowClear: true,
      width: '100%'
    });
  });

$(document).ready(function() {
  $('#studentno').select2({
    placeholder: "Select Student",
    allowClear: true,
    width: '100%',
    matcher: function(params, data) {
      if ($.trim(params.term) === '') return data;
      if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) return data;
      return null;
    }
  });
});
  // Auto-dismiss success alert after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
  var successToastEl = document.getElementById('successToast');
  var errorToastEl = document.getElementById('errorToast');
  if (successToastEl) {
    var toast = new bootstrap.Toast(successToastEl, { delay: 3000 });
    toast.show();
  }
  if (errorToastEl) {
    var toast = new bootstrap.Toast(errorToastEl, { delay: 3000 });
    toast.show();
  }
});

// Drop Photo 
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('photoInput');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', e => {
  e.preventDefault();
  dropZone.classList.add('bg-light');
});

dropZone.addEventListener('dragleave', () => {
  dropZone.classList.remove('bg-light');
});

dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('bg-light');
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    fileInput.files = files;

    // Optional Preview
    const reader = new FileReader();
    reader.onload = e => {
      dropZone.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded">`;
    };
    reader.readAsDataURL(files[0]);
  }
});

// Cropper
let cropper;
const cropImage = document.getElementById('cropImage');
const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));

fileInput.addEventListener('change', e => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      cropImage.src = e.target.result;
      cropModal.show();
    };
    reader.readAsDataURL(file);
  }
});

document.getElementById('cropModal').addEventListener('shown.bs.modal', () => {
  cropper = new Cropper(cropImage, {
    aspectRatio: 1,
    viewMode: 1,
    autoCropArea: 1,
  });
});

document.getElementById('cropModal').addEventListener('hidden.bs.modal', () => {
  cropper.destroy();
});

document.getElementById('cropConfirm').addEventListener('click', () => {
  cropper.getCroppedCanvas().toBlob(blob => {
    const file = new File([blob], 'cropped.png', { type: 'image/png' });
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    dropZone.innerHTML = `<img src="${URL.createObjectURL(blob)}" class="img-fluid rounded">`;
    cropModal.hide();
  });
});
</script>
</body>
</html>
