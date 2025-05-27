<?php
session_start();
include 'db.php'; // adjust if your DB config file is named differently

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check queuing system status
$result = $conn->query("SELECT queuing_enabled FROM settings WHERE id = 1");
$config = $result->fetch_assoc();
if ($config['queuing_enabled'] == 0) {
    header("Location: index.html?disabled=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Registration</title>
    <!-- Bootstrap 5 and Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, rgb(36, 157, 238), rgb(93, 211, 231));
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .container-box {
            background-color: white;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            width: 550px;
            padding: 30px;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
            margin: 50px auto;
        }
        .card-header p {
            font-size: 1rem;
        }
        #content {
            padding: 50px;
        }
        .reqcolor {
            color: red;
            font-weight: bold;
        }
        .formtitlehead {
            color: #00bfff;
        }
        .fixed-logo {
            z-index: 999;
            text-align: center;
        }
        .fixed-logo .logo {
            height: 70px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .fixed-logo .form-title {
            display: inline-block;
            margin: 0;
            font-size: 20px;
            color: white;
            border-left: 2px solid white;
            padding-left: 10px;
            vertical-align: middle;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Custom classes for field feedback */
        .emphasize-error {
            border: 3px solid red !important;
            box-shadow: 0 0 5px red;
        }
        .emphasize-valid {
            border: 3px solid green !important;
            box-shadow: 0 0 5px green;
        }
    </style>
</head>
<body>
<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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

<div class="fixed-logo">
    <img src="pictures/fixed.png" alt="Logo" class="logo">
    <h4 class="card-title mt- text-center form-title">
        <font color="white">NEW STUDENT / TRANSFEREE REGISTRATION FORM</font>
    </h4>
</div>

<!-- Form Start -->
<div class="container mt-3" style="animation: fadeIn 0.8s ease-in-out;">
    <div class="card shadow-lg">
        <header class="card-header">
            <p>Welcome to the Student ID Registration Form for STI College Naga!</p>
            <p class="pb-2">Please make sure to fill-out the blank fields completely and correctly.</p>
            <p style="font-size:12px;"><i>Items marked with </i><span class="reqcolor" style="font-size:16px; font-weight:bold;">*</span><i> are required</i></p>
        </header>
        <article class="card-body">
            <form id="studentform" action="register.php" method="post" novalidate>
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <!-- Hidden Email Field (constructed) -->
                <input type="hidden" id="email" name="email">
                <!-- Student Information -->
                <div class="row g-1 mt-1">
                    <div class="col-md-3">
                        <label><b>Last Name:</b><span class="reqcolor">*</span></label>
                        <input type="text" name="lastname" id="lastname" class="form-control name-field" placeholder="Surname" required>
                    </div>
                    <div class="col-md-3">
                        <label><b>First Name:</b><span class="reqcolor">*</span></label>
                        <input type="text" name="firstname" id="firstname" class="form-control name-field" placeholder="Given Name" required>
                    </div>
                    <div class="col-md-3">
                        <label><b>Ext. Name:</b></label>
                        <select class="form-control" name="extname" id="extname">
                            <option value="" disabled selected>Ext. Name</option>
                            <option value="Jr">Jr</option>
                            <option value="Sr">Sr</option>
                            <option value="I">I</option>
                            <option value="II">II</option>
                            <option value="III">III</option>
                            <option value="IV">IV</option>
                            <option value="V">V</option>
                            <option value="VI">VI</option>
                            <option value="VII">VII</option>
                            <option value="VIII">VIII</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><b>Middle Initial:</b></label>
                        <input type="text" name="middleinitial" id="middleinitial" class="form-control" placeholder="M.I (Leave Blank)" maxlength="3">
                    </div>
                </div>
                <div class="row g-1 mt-1">
                    <div class="col-md-3">
                        <label><b>Date of Birth:</b><span class="reqcolor">*</span></label>
                        <input type="date" id="dob" name="dob" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label><b>Email Address:</b><span class="reqcolor">*</span></label>
                        <div class="input-group" id="emailGroup">
                            <input type="text" id="emailLast" name="emailLast" class="form-control" placeholder="lastname" maxlength="20" readonly>
                            <span class="input-group-text">.</span>
                            <input type="text" id="emailStudentNo" name="emailStudentNo" class="form-control" placeholder="XXXXXX" maxlength="6">
                            <span class="input-group-text">@naga.sti.edu.ph</span>
                        </div>
                        <span id="emailError" class="error"></span>
                    </div>
                    <div class="col-md-2">
                        <label><b>Gender:</b><span class="reqcolor">*</span></label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><b>Phone Number:</b><span class="reqcolor">*</span></label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="09XX-XXX-XXXX" required>
                    </div>
                </div>
                <!-- Academic Information -->
                <h5 class="mt-3"><strong><p class="formtitlehead">Academic Information</p></strong></h5>
                <div class="row g-1 mt-1">
                    <div class="col-md-3">
                        <label><b>Student Number:</b><span class="reqcolor">*</span></label>
                        <input type="text" id="studentno" name="studentno" class="form-control" placeholder="Stu. No. (02000XXXXXX)" pattern="^02000\d{6}$" title="Student No. must start with '02000' followed by 6 digits" maxlength="11" readonly required>
                    </div>
                    <div class="col-md-3">
                        <label><b>Course/Strand:</b><span class="reqcolor">*</span></label>
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
                    <div class="col-md-3">
                        <label><b>Grade/Year Level:</b><span class="reqcolor">*</span></label>
                        <select class="form-control" id="yearlevel" name="yearlevel" required>
                            <option value="" disabled selected>Select Year Level</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label><b>Block/Section:</b><span class="reqcolor">*</span></label>
                        <select class="form-control" id="block" name="block" required>
                            <option value="" disabled selected>Select Block</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                        </select>
                    </div>
                </div>
                <!-- Address -->
                <h5 class="mt-3"><strong><p class="formtitlehead">Address</p></strong></h5>
                <div class="row g-1 mt-1">
                    <label><b>Full Address:</b><span class="reqcolor">*</span></label>
                    <textarea id="address" name="address" class="form-control" placeholder="Enter your address here..." required></textarea>
                </div>
                <!-- Emergency Contact Info -->
                <h5 class="mt-3"><strong><p class="formtitlehead">Emergency Contact</p></strong></h5>
                <div class="row g-1 mt-2">
                    <div class="col-md-4">
                        <label><b>Contact Name:</b><span class="reqcolor">*</span></label>
                        <input type="text" id="contactname" name="contactname" class="form-control name-field" placeholder="Emergency Contact Name" required>
                    </div>
                    <div class="col-md-4">
                        <label><b>Contact No.:</b><span class="reqcolor">*</span></label>
                        <input type="text" id="contactno" name="contactno" class="form-control" placeholder="09XX-XXX-XXXX" required>
                    </div>
                    <div class="col-md-4">
                        <label><b>Relationship:</b><span class="reqcolor">*</span></label>
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
                    <input class="form-check-input" type="checkbox" id="agree" name="agree" value="Y">
                    <label class="form-check-label" for="agree">
                        <small class="text-muted">By checking this box and clicking 'Proceed', you accept the <a href="https://www.sti.edu/dataprivacy.asp" data-bs-toggle="modal">Privacy Policy</a>.<span class="reqcolor">*</span></small>
                    </label>
                </div>
                <!-- Submit Button -->
                <div class="mt-2">
                    <button type="button" id="submitBtn" class="btn btn-primary w-100" disabled>Proceed</button>
                </div>
                <footer class="text-center text-muted">
                    <small>&copy; <span class="currentYear"></span> Student ID Registration System</small>
                </footer>
            </form>
        </article>
    </div>
</div>
</div>
</div>
<br>
<!-- End of Form -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // Set the current year in the footer.
        $(".currentYear").text(new Date().getFullYear());

        // Define appUrl for AJAX requests.
        const appUrl = window.location.hostname === 'localhost' ? `${window.location.origin}/stu_reg` : window.location.origin;

        /* --- Enable Submit Button when Agreement Checked --- */
        $("#agree").change(function () {
            $("#submitBtn").prop("disabled", !this.checked);
        });

        /* --- Dynamic Year Level Options Based on Course Selection --- */
        $("#course").change(function () {
            var course = $(this).val();
            var yearLevelDropdown = $("#yearlevel");
            yearLevelDropdown.empty();
            if (course === "ABM" || course === "HUMSS" || course === "STEM" || course === "IT MAWD") {
                yearLevelDropdown.append('<option value="Grade 11">Grade 11</option>');
                yearLevelDropdown.append('<option value="Grade 12">Grade 12</option>');
            } else if (course === "BS Information Technology" || course === "BS Hospitality Management") {
                yearLevelDropdown.append('<option value="1st Yr.">1st Year</option>');
                yearLevelDropdown.append('<option value="2nd Yr.">2nd Year</option>');
                yearLevelDropdown.append('<option value="3rd Yr.">3rd Year</option>');
                yearLevelDropdown.append('<option value="4th Yr.">4th Year</option>');
            } else {
                yearLevelDropdown.append('<option value="" disabled selected>Select Year Level</option>');
            }
        });

        /* --- Enable Block Selection Only When Course and Year Level Are Selected --- */
        $("#block").prop("disabled", true);
        $("#course, #yearlevel").change(function () {
            var courseSelected = $("#course").val();
            var yearLevelSelected = $("#yearlevel").val();
            $("#block").prop("disabled", !(courseSelected && yearLevelSelected));
        });

        /* --- Auto-Capitalization for Names (Main Inputs) --- */
        $(".name-field").on("input", function () {
            var inputVal = $(this).val();
            var capitalizedVal = inputVal.replace(/\b\w/g, function (char) { return char.toUpperCase(); });
            $(this).val(capitalizedVal.replace(/[^a-zA-Z\s]/g, ''));
        });

        /* --- Phone and Emergency Contact Number Masking --- */
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

        /* --- Email Group Updates --- */
        $("#lastname").on("input", function() {
            var val = $(this).val().toLowerCase().replace(/[^a-z]/g, '');
            $("#emailLast").val(val);
            updateHiddenEmail();
            validateEmailMask();
        });

        $("#emailStudentNo").on("input", function() {
            var val = $(this).val().replace(/[^0-9]/g, '');
            val = val.substring(0, 6);
            $(this).val(val);
            if (val.length === 6) {
                $("#studentno").val("02000" + val);
            } else {
                $("#studentno").val("");
            }
            updateHiddenEmail();
            validateEmailMask();
        });

        function updateHiddenEmail() {
            var last = $("#emailLast").val();
            var num = $("#emailStudentNo").val();
            if (last && num.length === 6) {
                $("#email").val(last + "." + num + "@naga.sti.edu.ph");
            } else {
                $("#email").val("");
            }
        }

        function validateEmailMask() {
            var emailLast = $("#emailLast").val();
            var emailNum = $("#emailStudentNo").val();
            var errorMsg = "";
            if (!emailLast) errorMsg += "Last name required in email. ";
            if (emailNum.length !== 6) errorMsg += "Enter your custom 6-digit number here.";
            $("#emailError").text(errorMsg);
            return (errorMsg === "");
        }

        /* --- Field-Level Color Feedback --- */
        $("input, select, textarea").on("input change blur", function() {
            if ($(this).attr("type") === "hidden") return;
            var value = $(this).val().trim();
            if (value === "") {
                $(this).removeClass("border-success border-danger emphasize-error emphasize-valid");
            } else if (this.checkValidity()) {
                $(this).removeClass("border-danger emphasize-error").addClass("border-success");
            } else {
                $(this).removeClass("border-success").addClass("border-danger emphasize-error");
            }
        });

        /* --- Focus Feedback for Valid Fields --- */
        $("input, select, textarea").on("focus", function() {
            if ($(this).attr("type") === "hidden") return;
            var value = $(this).val().trim();
            if (value !== "" && this.checkValidity()) {
                $(this).addClass("emphasize-valid");
            }
        }).on("blur", function() {
            $(this).removeClass("emphasize-valid");
        });

        /* --- Confirmation Modal & Duplicate Student Number Check on Proceed Click --- */
        $("#submitBtn").click(function () {
            var form = $('#studentform')[0];
            if (!form.checkValidity()) {
                $("#studentform").find("input, select, textarea").each(function(){
                    if (!this.checkValidity()) {
                        $(this).addClass("emphasize-error");
                    } else {
                        $(this).removeClass("emphasize-error");
                    }
                });
                alert("Please fill all the required fields.");
                return;
            }
            if (!validateEmailMask()) {
                $("#emailGroup").focus();
                return;
            }
            var studentNo = $("#studentno").val();
            $.ajax({
                url: `${appUrl}/admin/check_studentno.php`,
                method: "POST",
                data: { studentno: studentNo },
                success: function(response) {
                    if (response.trim() === "exists") {
                        alert("Duplicate entry: A student with this student number already exists.");
                        return;
                    } else {
                        var details = `
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Last Name:</strong> ${$("#lastname").val()}</p>
                                <p><strong>First Name:</strong> ${$("#firstname").val()}</p>
                                <p><strong>Ext. Name:</strong> ${$("#extname").val() ? $("#extname").val() : ''}</p>
                                <p><strong>Middle Initial:</strong> ${$("#middleinitial").val()}</p>
                                <p><strong>Date of Birth:</strong> ${$("#dob").val()}</p>
                                <p><strong>Email:</strong> ${$("#email").val()}</p>
                                <p><strong>Phone:</strong> ${$("#phone").val()}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Student No.:</strong> ${$("#studentno").val()}</p>
                                <p><strong>Course/Strand:</strong> ${$("#course").val()}</p>
                                <p><strong>Year Level:</strong> ${$("#yearlevel").val()}</p>
                                <p><strong>Block/Section:</strong> ${$("#block").val()}</p>
                                <p><strong>Address:</strong> ${$("#address").val()}</p>
                                <p><strong>Emergency Contact Name:</strong> ${$("#contactname").val()}</p>
                                <p><strong>Emergency Contact No.:</strong> ${$("#contactno").val()}</p>
                            </div>
                        </div>
                        `;
                        $("#confirmDetails").html(details);
                        $("#confirmModal").modal("show");
                    }
                }
            });
        });

        $("#confirmSubmit").click(function () {
            $("#studentform").submit();
        });
    });
</script>
</body>
</html>
