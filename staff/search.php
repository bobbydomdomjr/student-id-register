<?php
// staff/search.php
session_start();
require_once('../db.php');

// Only staff
if (!isset($_SESSION['admin']) || $_SESSION['role']!=='staff') {
    header("Location: ./login/index.php");
    exit();
}

$results = [];
$query = '';
if (isset($_GET['q'])) {
    $query = trim($_GET['q']);
    $like = "%{$query}%";
    $stmt = $conn->prepare("
        SELECT studentno, firstname, lastname, course, yearlevel, status
        FROM student_registration
        WHERE studentno LIKE ? OR firstname LIKE ? OR lastname LIKE ?
        ORDER BY registration_date DESC
        LIMIT 50
    ");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Search Students</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h3>Search & Filter</h3>
  <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3">← Back to Dashboard</a>

  <form class="input-group mb-3" method="GET">
    <input type="text" name="q" value="<?= htmlspecialchars($query) ?>"
           class="form-control" placeholder="Name or Student No." required>
    <button class="btn btn-primary">Search</button>
  </form>

  <?php if ($query !== ''): ?>
    <h5>Results for “<?= htmlspecialchars($query) ?>” (<?= count($results) ?>)</h5>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Student No.</th><th>Name</th><th>Course</th><th>Year</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['studentno']) ?></td>
            <td><?= htmlspecialchars($r['firstname'].' '.$r['lastname']) ?></td>
            <td><?= htmlspecialchars($r['course']) ?></td>
            <td><?= htmlspecialchars($r['yearlevel']) ?></td>
            <td><?= htmlspecialchars(ucfirst($r['status'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($results)): ?>
          <tr><td colspan="5" class="text-center text-muted">No matches found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
