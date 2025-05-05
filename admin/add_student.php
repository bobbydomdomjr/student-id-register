<?php
// Database connection
$servername = "localhost";
$username = "root";  // Default username for XAMPP
$password = "";      // Default password for XAMPP
$dbname = "ocsergs_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$errorMessage = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data with checks
    $studentno = isset($_POST['studentno']) ? $_POST['studentno'] : '';
    $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $middleinitial = isset($_POST['middleinitial']) ? $_POST['middleinitial'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;  // Make sure it's set
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    $yearlevel = isset($_POST['yearlevel']) ? $_POST['yearlevel'] : '';
    $block = isset($_POST['block']) ? $_POST['block'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $contactname = isset($_POST['contactname']) ? $_POST['contactname'] : '';
    $contactno = isset($_POST['contactno']) ? $_POST['contactno'] : '';
    $relationship = isset($_POST['relationship']) ? $_POST['relationship'] : '';

    // Check if required fields are not empty before proceeding
    if (empty($studentno) || empty($lastname) || empty($firstname) || empty($dob) || empty($email) || empty($gender) || empty($phone) || empty($course) || empty($yearlevel) || empty($block) || empty($address) || empty($contactname) || empty($contactno) || empty($relationship)) {
        $errorMessage = "All fields are required!";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO student_registration (studentno, lastname, firstname, middleinitial, dob, email, gender, phone, course, yearlevel, block, address, contactname, contactno, relationship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssssss", $studentno, $lastname, $firstname, $middleinitial, $dob, $email, $gender, $phone, $course, $yearlevel, $block, $address, $contactname, $contactno, $relationship);

        // Execute the query
        if ($stmt->execute()) {
            $errorMessage = "Student added successfully!";
        } else {
            $errorMessage = ": " . $stmt->error;
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


    <style>
        body {
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        #content {
            padding: 20px;
        }

        footer {
            margin-top: 20px;
        }

        textarea#address {
            height: 80px;
        }
    </style>
</head>

<body>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmDetails"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmSubmit">Submit</button>
                </div>
            </div>
        </div>
    </div>

     <!-- Warning/Error Modal -->
     <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorMessage"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Area -->
    <div id="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-lg">
                        <header class="card-header">
                        <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
    <a href="students.php" class="btn btn-secondary w-auto">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <h4 class="card-title mt-2 text-center">
        <strong><font color="red">ADD STUDENT</font></strong>
    </h4>
</div>

                        </header>
                        <article class="card-body">
                            <form name="studentform" id="studentform" action="add_student.php" method="post" autocomplete="off">

                                <!-- Student Information -->
                                <h5 class="mt-3"><strong>Student Information</strong></h5>
                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label>Student No.</label>
                                        <input type="text" id="studentno" name="studentno" class="form-control"
                                            placeholder="Enter Student No. (02XXXXXXXXX)" pattern="^02\d{9}$"
                                            title="Student No. must start with '02' followed by 9 digits" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label>Last Name</label>
                                        <input type="text" id="lastname" name="lastname" class="form-control name-field"
                                            placeholder="Last Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>First Name</label>
                                        <input type="text" id="firstname" name="firstname" class="form-control name-field"
                                            placeholder="First Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Middle Initial</label>
                                        <input type="text" id="middleinitial" name="middleinitial" class="form-control"
                                            placeholder="M.I." maxlength="3">
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-3">
                                        <label>Date of Birth</label>
                                        <input type="date" id="dob" name="dob" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Email Address</label>
                                        <input type="email" id="email" name="email" class="form-control"
                                            placeholder="example@domain.com" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Gender</label>
                                        <select class="form-control" id="gender" name="gender" required>
                                            <option value="" disabled selected>Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Phone Number</label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                            placeholder="09XX-XXX-XXXX" required>
                                    </div>
                                </div>

                                <!-- Course and Year Level -->
                                <h5 class="mt-4"><strong>Academic Information</strong></h5>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label>Course/Strand</label>
                                        <select class="form-control" id="course" name="course" required>
                                            <option value="" disabled selected>Select Course/Strand</option>
                                            <option value="ABM">ABM</option>
                                            <option value="HUMSS">HUMSS</option>
                                            <option value="STEM">STEM</option>
                                            <option value="IT MAWD">IT MAWD</option>
                                            <option value="BS Information Technology">BS Information Technology</option>
                                            <option value="BS Hospitality Management">BS Hospitality Management</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Grade/Year Level</label>
                                        <select class="form-control" id="yearlevel" name="yearlevel" required>
                                            <option value="" disabled selected>Select Year Level</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Block</label>
                                        <select class="form-control" id="block" name="block" required>
                                            <option value="" disabled selected>Select Block</option>
                                            <option value="A">Block A</option>
                                            <option value="B">Block B</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Address -->
                                <h5 class="mt-4"><strong>Address</strong></h5>
                                <div class="mt-2">
                                    <label>Full Address</label>
                                    <textarea id="address" name="address" class="form-control"
                                        placeholder="Enter your address here..." required></textarea>
                                </div>

                                <!-- Emergency Contact Info -->
                                <h5 class="mt-4"><strong>Emergency Contact</strong></h5>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label>Contact Name</label>
                                        <input type="text" id="contactname" name="contactname" class="form-control name-field"
                                            placeholder="Emergency Contact Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Contact No.</label>
                                        <input type="text" id="contactno" name="contactno" class="form-control"
                                            placeholder="09XX-XXX-XXXX" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Relationship</label>
                                        <select class="form-control" id="relationship" name="relationship" required>
                                            <option value="" disabled selected>Select Relationship</option>
                                            <option value="Parent">Parent</option>
                                            <option value="Guardian">Guardian</option>
                                            <option value="Sibling">Sibling</option>
                                            <option value="Relative">Relative</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Agreement Checkbox -->
                                <div class="form-check mt-3">
                                    <input type="checkbox" class="form-check-input" id="agree" name="agree" value="Y">
                                    <label class="form-check-label" for="agree">
                                        <small class="text-muted">By checking this box and clicking 'Submit',
                                            you accept the <a href="#privacy" data-bs-toggle="modal">Privacy Policy.</a></small>
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <div class="mt-3">
                                    <button type="button" id="submitBtn" class="btn btn-primary w-100" disabled>Submit</button>
                                </div>

                            </form>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center text-muted">
        <small>&copy; 2025 Student Registration System</small>
    </footer>

    <script>
        // Enable submit button when the checkbox is checked
        $("#agree").change(function () {
            $("#submitBtn").prop("disabled", !this.checked);
        });

        // Dynamic Year Level Options Based on Course Selection
        $("#course").change(function () {
            var course = $(this).val();
            var yearLevelDropdown = $("#yearlevel");

            yearLevelDropdown.empty();

            if (course === "ABM" || course === "HUMSS" || course === "STEM" || course === "IT MAWD") {
                yearLevelDropdown.append('<option value="Grade 11">Grade 11</option>');
                yearLevelDropdown.append('<option value="Grade 12">Grade 12</option>');
            } else if (course === "BS Information Technology" || course === "BS Hospitality Management") {
                yearLevelDropdown.append('<option value="1st Year">1st Year</option>');
                yearLevelDropdown.append('<option value="2nd Year">2nd Year</option>');
                yearLevelDropdown.append('<option value="3rd Year">3rd Year</option>');
                yearLevelDropdown.append('<option value="4th Year">4th Year</option>');
            } else {
                yearLevelDropdown.append('<option value="" disabled selected>Select Year Level</option>');
            }
        });

        // Disable Block until Course and Year Level are selected
        $("#block").prop("disabled", true);

        $("#course, #yearlevel").change(function () {
            var courseSelected = $("#course").val();
            var yearLevelSelected = $("#yearlevel").val();

            if (courseSelected && yearLevelSelected) {
                $("#block").prop("disabled", false);
            } else {
                $("#block").prop("disabled", true);
            }
        });

        // Name Auto Capitalization and Validation
        $(".name-field").on("input", function () {
            var inputVal = $(this).val();
            var capitalizedVal = inputVal.replace(/\b\w/g, function (char) {
                return char.toUpperCase();
            });
            $(this).val(capitalizedVal.replace(/[^a-zA-Z\s]/g, ''));
        });

        // Phone and Contact No. Masking
        $("#phone, #contactno").on("input", function () {
            var val = $(this).val().replace(/\D/g, "");
            if (val.length > 10) val = val.substring(0, 11);
            if (val.length <= 4) {
                val = val.replace(/(\d{4})/, "$1");
            } else {
                val = val.replace(/(\d{4})(\d{3})(\d{0,4})/, "$1-$2-$3");
            }
            $(this).val(val);
        });

        // Show Confirmation Modal with User Details
        $("#submitBtn").click(function () {
            var details = `
                <p><strong>Student No.:</strong> ${$("#studentno").val()}</p>
                <p><strong>Last Name:</strong> ${$("#lastname").val()}</p>
                <p><strong>First Name:</strong> ${$("#firstname").val()}</p>
                <p><strong>Date of Birth:</strong> ${$("#dob").val()}</p>
                <p><strong>Email:</strong> ${$("#email").val()}</p>
                <p><strong>Phone:</strong> ${$("#phone").val()}</p>
                <p><strong>Course/Strand:</strong> ${$("#course").val()}</p>
                <p><strong>Year Level:</strong> ${$("#yearlevel").val()}</p>
                <p><strong>Block:</strong> ${$("#block").val()}</p>
                <p><strong>Address:</strong> ${$("#address").val()}</p>
                <p><strong>Emergency Contact Name:</strong> ${$("#contactname").val()}</p>
                <p><strong>Emergency Contact No.:</strong> ${$("#contactno").val()}</p>
            `;
            $("#confirmDetails").html(details);
            $("#confirmModal").modal("show");
        });
        

        // Submit form on confirmation
        $("#confirmSubmit").click(function () {
            $("#studentform").submit();
        });

        // Show the error modal if there is an error message
        <?php if (!empty($errorMessage)) { ?>
            $(document).ready(function() {
                $("#errorMessage").html("<?php echo $errorMessage; ?>");
                $("#errorModal").modal("show");
            });
        <?php } ?>
    </script>
</body>

</html>
