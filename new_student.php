<?php
include 'db.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right,rgb(36, 157, 238),rgb(93, 211, 231));
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
        }
        .card-header p {
        font-size: 1 rem;
}
        #content {
            padding: 50px;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: block;
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
        .reqcolor {
            color: red;
        }
        .formtitlehead{
            color: #00bfff;
        }        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .error {
        border: 2px solid red;
        animation: shake 0.3s;
    }

    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }

    .error-msg {
        color: red;
        font-size: 0.9em;
        display: none;
    }

    </style>
</head>
<body>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
</h4></div>
<!-- Form Start -->
&nbsp
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-lg">
                    <header class="card-header">
                        <p>Welcome to the Student ID Regisration Form for STI College Naga!</p>
                            <p class="pb-2">Please make sure to fill-out the blank fields completely and correctly.</p>
                            <p style="font-size:12px;"><i>Items marked an</i> <span class="reqcolor" style="font-size:16px;font-weight:bold;">*</span><i>are required</i></p>
                    </header>
                    <article class="card-body">
                        <form id="studentform" action="register.php" method="post" novalidate>
                            <!-- Sample Input -->
                            <div class="row g-1 mt-1">
                                <div class="col-md-3">
                                    <label><b>Last Name:</b><span class="reqcolor">*</span></label>
                                    <input type="text" name="lastname" id="lastname" class="form-control name-field" placeholder="Surname" required>
                                
                                </div>
                                <div class="col-md-3">
                                    <label><b>First Name</b><span class="reqcolor">*</span></label>
                                    <input type="text" name="firstname" id="firstname" class="form-control name-field" placeholder="Given Name" required>
                               
                                </div>
                                <div class="col-md-3">
                                    <label><b>Ext. Name</b></label>
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
                                    <label><b>Middle Initial</b></label>
                                    <input type="text" name="middleinitial" id="middleinitial" class="form-control name-field" placeholder="M.I (Leave Blank)" maxlength="3">
                                </div>
                            </div>
                            <div class="row g-1 mt-1">
                                    <div class="col-md-3">
                                        <label><b>Date of Birth:</b><span class="reqcolor">*</span></label>
                                        <input type="date" id="dob" name="dob" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                
                                    <label><b>Email Address:</b><span class="reqcolor">*</span></label>
<input type="text" id="email" name="email" class="form-control"
    placeholder="lastname.123456@naga.sti.edu.ph"
    pattern="^[a-zA-Z]+\.\d{6}@naga\.sti\.edu\.ph$"
    title="Email must be in the format: lastname.123456@naga.sti.edu.ph"
    maxlength="50" required>
<div id="emailError" class="error-msg" style="color: red; display: none;">
  Invalid email format. Example: lastname.123456@naga.sti.edu.ph
</div>

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
                                        <input type="text" id="phone" name="phone" class="form-control"
                                            placeholder="09XX-XXX-XXXX" required>
                                    </div>
                                </div>

                                <!-- Course and Year Level -->
                                <h5 class="mt-3"><strong><p class="formtitlehead">Academic Information</p></strong></h5>
                                <div class="row g-1 mt-1">
                                    <div class="col-md-3">
                                    <label><b>Student Number:</b><span class="reqcolor">*</span></label>
<input type="text" id="studentno" name="studentno" class="form-control"
    placeholder="Stu. No. (02000XXXXXX)" pattern="^02000\d{6}$"
    title="Student No. must start with '02000' followed by 6 digits" maxlength="11" readonly required>
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
                                        <label><b>Block/Section:</b><span class="reqcolor"></span></label>
                                        <select class="form-control" id="block" name="block">
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
                                    <textarea id="address" name="address" class="form-control"
                                        placeholder="Enter your address here..." required></textarea>   
                                </div>

                                <!-- Emergency Contact Info -->
                                <h5 class="mt-3"><strong><p class="formtitlehead">Emergency Contact</p></strong></h5>
                                <div class="row g-1 mt-2">
                                    <div class="col-md-4">
                                        <label><b>Contact Name:</b><span class="reqcolor">*</span></label>
                                        <input type="text" id="contactname" name="contactname" class="form-control name-field"
                                            placeholder="Emergency Contact Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label><b>Contact No:</b><span class="reqcolor">*</span></label>
                                        <input type="text" id="contactno" name="contactno" class="form-control"
                                            placeholder="09XX-XXX-XXXX" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label><b>Relationship</b><span class="reqcolor">*</span></label>
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

                            <!-- Add other fields and apply same style -->

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="Y">
                                <label class="form-check-label" for="agree">
                                    <small class="text-muted">By checking this box and clicking 'Proceed',
                                    you accept the <a href="https://www.sti.edu/dataprivacy.asp">Privacy Policy</a>.<span class="reqcolor">*</span></small>
                                </label>
                            </div>
                            <div class="mt-2">
                                <button type="button" id="submitBtn" class="btn btn-primary w-100" disabled>Proceed</button>
                            </div>

                    </article>
                </div>
            </div>
        </div>
    </div>
&nbsp
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    // Dynamic Year Level Options Based on Course Selection
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

//email

document.getElementById('email').addEventListener('input', function () {
    let value = this.value;

    // Remove invalid characters
    value = value.replace(/[^a-zA-Z0-9.@]/g, '');

    const parts = value.split('@')[0].split('.');
    
    // If format is lastname.123456 and domain not yet added
    if (parts.length === 2 && /^[a-zA-Z]+$/.test(parts[0]) && /^\d{6}$/.test(parts[1])) {
        value = parts[0] + '.' + parts[1] + '@naga.sti.edu.ph';
    }

    this.value = value;

    const emailPattern = /^[a-zA-Z]+\.(\d{6})@naga\.sti\.edu\.ph$/;
    const match = value.match(emailPattern);
    const errorMsg = document.getElementById('emailError');
    const studentField = document.getElementById('studentno');

    if (match) {
        const last6Digits = match[1];
        studentField.value = '02000' + last6Digits;
        errorMsg.style.display = 'none';
    } else {
        studentField.value = '';
        errorMsg.style.display = 'block';
    }
});
    // Enable submit button when checkbox is checked
    $("#agree").change(function () {
        $("#submitBtn").prop("disabled", !this.checked);
    });

    // Capitalize Names
    $(".name-field").on("input", function () {
        let val = $(this).val();
        val = val.replace(/\b\w/g, c => c.toUpperCase());
        $(this).val(val.replace(/[^a-zA-Z\s]/g, ""));
    });

    // Phone number format
    $("#phone, #contactno").on("input", function () {
        let val = $(this).val().replace(/\D/g, "").slice(0, 11);
        if (val.length <= 4) val = val;
        else val = val.replace(/(\d{4})(\d{3})(\d{0,4})/, "$1-$2-$3");
        $(this).val(val);
    });

    // Validate form on submit click
// Form Validation and Confirmation Modal
$("#submitBtn").click(function () {
    let valid = true;
    $("#studentform")[0].querySelectorAll("input, select, textarea").forEach(function (el) {
        if (el.hasAttribute("required") && !el.value.trim()) {
            el.classList.add("is-invalid");
            valid = false;
        } else {
            el.classList.remove("is-invalid");
        }
    });

    if (valid) {
        // Populate confirmation modal with form data
        const details = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Last Name:</strong> ${$("#lastname").val()}</p>
                    <p><strong>First Name:</strong> ${$("#firstname").val()}</p>
                    <p><strong>Ext. Name:</strong> ${$("#extname").val()}</p>
                    <p><strong>Middle Initial:</strong> ${$("#middleinitial").val()}</p>
                    <p><strong>Date of Birth:</strong> ${$("#dob").val()}</p>
                    <p><strong>Email:</strong> ${$("#email").val()}</p>
                    <p><strong>Phone:</strong> ${$("#phone").val()}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Student No.:</strong> ${$("#studentno").val()}</p>
                    <p><strong>Course/Strand:</strong> ${$("#course").val()}</p>
                    <p><strong>Year Level:</strong> ${$("#yearlevel").val()}</p>
                    <p><strong>Block:</strong> ${$("#block").val()}</p>
                    <p><strong>Full Address:</strong> ${$("#address").val()}</p>
                    <p><strong>Emergency Contact:</strong> ${$("#contactname").val()}</p>
                    <p><strong>Contact No:</strong> ${$("#contactno").val()}</p>
                    <p><strong>Relationship:</strong> ${$("#relationship").val()}</p>
                </div>
            </div>
        `;
        
        // Set modal content
        $("#confirmDetails").html(details);
        
        // Show modal
        $('#confirmModal').modal('show');

        // Handle form submission on confirmation
        $("#confirmSubmit").click(function () {
            $("#studentform").submit();
        });
    }
});
  // Retain values on error
  var formData = <?php echo json_encode($_POST); ?>;
        for (const key in formData) {
            $('#' + key).val(formData[key]);
        }
</script>

</body>
</html>
