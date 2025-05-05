<?php
// Include database connection
include('db.php');

// Initialize variables
$result = "";

if (isset($_POST['search'])) {
    $searchValue = $_POST['searchValue'];

    // Prepare SQL to search by reg_no or lastname
    $sql = "SELECT * FROM students WHERE reg_no = ? OR lastname = ?";

    $stmt = $conn->prepare($sql);

    // Check if prepare was successful
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind the search value for both fields
    $stmt->bind_param("ss", $searchValue, $searchValue);
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check for query errors
    if ($conn->error) {
        die("Query failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student Info</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Search Student Information</h2>
    <form method="POST" action="search.php" class="form-inline justify-content-center">
        <input type="text" name="searchValue" class="form-control mr-2" placeholder="Enter Registration No. or Last Name" required>
        <button type="submit" name="search" class="btn btn-primary">Search</button>
    </form>

    <div class="mt-4">
        <?php
        if (isset($result) && $result->num_rows > 0) {
            echo '<table class="table table-bordered table-striped">';
            echo '<thead><tr><th>Reg No</th><th>Last Name</th><th>First Name</th><th>Middle Name</th><th>Date of Birth</th><th>Course</th><th>Contact No</th></tr></thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['reg_no']) . '</td>';
                echo '<td>' . htmlspecialchars($row['lastname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['firstname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['middlename']) . '</td>';
                echo '<td>' . htmlspecialchars($row['dob']) . '</td>';
                echo '<td>' . htmlspecialchars($row['course']) . '</td>';
                echo '<td>' . htmlspecialchars($row['contactno']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } elseif (isset($_POST['search'])) {
            echo '<div class="alert alert-danger">No record found.</div>';
        }
        ?>
    </div>
</div>

</body>
</html>
