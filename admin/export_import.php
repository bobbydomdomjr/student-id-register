<?php
// Export students to CSV
if (isset($_POST['export'])) {
    include('../db.php');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_data.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('Reg No', 'Last Name', 'First Name', 'Middle Initial', 'DOB', 'Gender', 'Contact No', 'Email', 'Course', 'Year Level', 'Address'));

    $sql = "SELECT reg_no, lastname, firstname, middleinitial, dob, gender, contactno, email, course, yearlevel, address FROM student_registration";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Import students from CSV
if (isset($_POST['import'])) {
    include('../db.php');
    
    $fileName = $_FILES['csv_file']['tmp_name'];

    if ($_FILES['csv_file']['size'] > 0) {
        $file = fopen($fileName, 'r');
        fgetcsv($file); // Skip header

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            $sql = "INSERT INTO student_registration (reg_no, lastname, firstname, middleinitial, dob, gender, contactno, email, course, yearlevel, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssss", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10]);
            $stmt->execute();
        }
        fclose($file);
        echo "<script>alert('Data imported successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Please upload a valid CSV file.'); window.location.href='dashboard.php';</script>";
    }
}

// User Management System
if (isset($_POST['add_user'])) {
    include('../db.php');

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error adding user. Please try again.'); window.location.href='dashboard.php';</script>";
    }
}

if (isset($_POST['delete_user'])) {
    include('../db.php');

    $user_id = $_POST['user_id'];
    
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting user.'); window.location.href='dashboard.php';</script>";
    }
}
?>

<!-- Import/Export Buttons -->
<form method="post" enctype="multipart/form-data" class="mb-3">
    <button type="submit" name="export" class="btn btn-success">Export to CSV</button>
</form>

<form method="post" enctype="multipart/form-data" class="mb-3">
    <input type="file" name="csv_file" class="form-control mb-2" accept=".csv" required>
    <button type="submit" name="import" class="btn btn-primary">Import from CSV</button>
</form>

<!-- User Management Section -->
<h3>User Management</h3>
<form method="post" class="mb-3">
    <input type="text" name="username" class="form-control mb-2" placeholder="Enter Username" required>
    <input type="password" name="password" class="form-control mb-2" placeholder="Enter Password" required>
    <select name="role" class="form-control mb-2" required>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>
    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
</form>

<h3>Delete User</h3>
<form method="post" class="mb-3">
    <input type="number" name="user_id" class="form-control mb-2" placeholder="Enter User ID to Delete" required>
    <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
</form>
