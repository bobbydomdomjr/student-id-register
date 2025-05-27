<?php
// staff/registration.php
session_start();
require_once('../db.php');

// Only staff
if (!isset($_SESSION['admin']) || $_SESSION['role'] !== 'staff') {
    header("Location: ./login/index.php");
    exit();
}

// Handle submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode']; // "new" or "old"
    // Required fields
    $fields = ['lastname','firstname','dob','gender','email','phone','studentno','course','yearlevel','block','address','contactname','contactno','relationship'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) $errors[] = ucfirst($f)." is required.";
    }
    if ($mode === 'old' && empty($_POST['or_number'])) {
        $errors[] = "OR number is required for reprint.";
    }
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO student_registration 
            (studentno, lastname, firstname, dob, gender, email, phone, course, yearlevel, block, address, contactname, contactno, relationship, registration_date, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),'pending')
        ");
        $stmt->bind_param(
            "sssssssssssss",
            $_POST['studentno'],
            $_POST['lastname'],
            $_POST['firstname'],
            $_POST['dob'],
            $_POST['gender'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['course'],
            $_POST['yearlevel'],
            $_POST['block'],
            $_POST['address'],
            $_POST['contactname'],
            $_POST['contactno'],
            $_POST['relationship']
        );
        $stmt->execute();
        $stmt->close();
        header("Location: registration.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Register Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    function toggleOR() {
      const mode = document.querySelector('input[name="mode"]:checked').value;
      document.getElementById('orField').style.display = mode==='old' ? 'block' : 'none';
    }
  </script>
</head>
<body class="p-4">
  <h3>Registration Management</h3>
  <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul>
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul></div>
  <?php elseif (isset($_GET['success'])): ?>
    <div class="alert alert-success">Student registered successfully!</div>
  <?php endif; ?>

  <form method="POST" class="row g-3">
    <div class="col-12">
      <label class="form-label">Type</label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="mode" value="new" checked onclick="toggleOR()">
        <label class="form-check-label">New Student</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="mode" value="old" onclick="toggleOR()">
        <label class="form-check-label">Reprint (Old Student)</label>
      </div>
    </div>

    <div id="orField" class="col-md-6" style="display:none;">
      <label class="form-label">OR Number</label>
      <input type="text" name="or_number" class="form-control">
    </div>

    <?php
    // Fields list: label => name
    $inputs = [
      'Student No.'=>'studentno','Last Name'=>'lastname','First Name'=>'firstname',
      'Birthdate'=>'dob','Gender (M/F)'=>'gender','Email'=>'email','Phone'=>'phone',
      'Course/Strand'=>'course','Year/Grade Level'=>'yearlevel','Block'=>'block',
      'Address'=>'address','Emergency Contact Name'=>'contactname',
      'Emergency Contact No.'=>'contactno','Relationship'=>'relationship'
    ];
    foreach ($inputs as $label=>$name): ?>
      <div class="col-md-6">
        <label class="form-label"><?= $label ?></label>
        <input type="text" name="<?= $name ?>" class="form-control"
               <?= in_array($name,['address'])? '':'required' ?>>
      </div>
    <?php endforeach; ?>

    <div class="col-12">
      <button class="btn btn-primary">Submit Registration</button>
    </div>
  </form>
</body>
</html>
