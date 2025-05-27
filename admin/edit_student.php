<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
include('../db.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the student ID from the URL
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Fetch student details
    $sql_student = "SELECT * FROM student_registration WHERE id = $student_id";
    $result_student = $conn->query($sql_student);
    if ($result_student->num_rows == 1) {
        $student = $result_student->fetch_assoc();
    } else {
        // Redirect if student not found
        header("Location: students.php");
        exit();
    }
} else {
    // Redirect if no student ID is provided
    header("Location: students.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the updated student data from the form
    $studentno = $_POST['studentno'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $middleinitial = $_POST['middleinitial'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $yearlevel = $_POST['yearlevel'];
    $block = $_POST['block'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $contactname = $_POST['contactname'];
    $contactno = $_POST['contactno'];
    $relationship = $_POST['relationship'];

    // Update the student record in the database
    $sql_update = "UPDATE student_registration SET studentno = '$studentno',
    firstname = '$firstname', lastname = '$lastname', middleinitial = '$middleinitial',
    email = '$email', gender = '$gender', phone = '$phone', course = '$course',
    yearlevel = '$yearlevel', block = '$block', dob = '$dob', address = '$address',
    contactname = '$contactname', contactno = '$contactno', relationship = '$relationship'
    WHERE id = $student_id";

    if ($conn->query($sql_update) === TRUE) {
        // Redirect to the student management page after updating
        header("Location: students.php");
        exit();
    } else {
        $error_message = "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #4e73df, #1cc88a);
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 30px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn {
            border-radius: 30px; /* Rounded corners */
            padding: 10px 20px; /* Increased padding for better appearance */
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out; /* Smooth transitions */
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-3px); /* Subtle lifting effect */
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-3px); /* Subtle lifting effect */
        }
        .card {
            border: none;
            border-radius: 10px;
            max-width: 900px; /* Slightly increased the width */
            margin: 0 auto; /* Center the card */
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
        }
        .card-body {
            padding: 20px 30px; /* Reduced padding for a more compact look */
        }
        .alert {
            margin-top: 20px;
            font-size: 16px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            border-radius: 5px;
        }
        .d-flex {
            justify-content: flex-start; /* Adjusted to make buttons aligned properly */
        }
        .d-flex button {
            margin-right: 10px; /* Space between buttons */
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card shadow-lg">
            <div class="card-header text-center">
                <h2>Edit Student Details</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="studentno">Student No</label>
                                <input type="text" class="form-control" id="studentno" name="studentno" value="<?php echo htmlspecialchars($student['studentno']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="firstname">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($student['firstname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($student['lastname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="middleinitial">Middle Initial</label>
                                <input type="text" class="form-control" id="middleinitial" name="middleinitial" value="<?php echo htmlspecialchars($student['middleinitial']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="course">Course</label>
                                <input type="text" class="form-control" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="yearlevel">Year Level</label>
                                <input type="text" class="form-control" id="yearlevel" name="yearlevel" value="<?php echo htmlspecialchars($student['yearlevel']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="block">Block</label>
                                <input type="text" class="form-control" id="block" name="block" value="<?php echo htmlspecialchars($student['block']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contactname">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="contactname" name="contactname" value="<?php echo htmlspecialchars($student['contactname']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="contactno">Emergency Contact No</label>
                                <input type="text" class="form-control" id="contactno" name="contactno" value="<?php echo htmlspecialchars($student['contactno']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="relationship">Relationship</label>
                                <input type="text" class="form-control" id="relationship" name="relationship" value="<?php echo htmlspecialchars($student['relationship']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Update Student</button>
                        <a href="students.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
