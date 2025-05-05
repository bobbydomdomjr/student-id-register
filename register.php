<?php
// Database connection
// Include database connection
include('db.php');

// Assuming you're using the same $conn connection
$result = $conn->query("SELECT * FROM settings WHERE id = 1"); // Assuming settings record has id = 1
$config = $result->fetch_assoc();

// Check if queuing system is enabled
if ($config['queuing_enabled'] == 0) {
    // Redirect to an information page or show a message
    echo "<p>The registration system is currently closed. Please try again later.</p>";
    // Optionally redirect to another page, e.g., home page
    header('Location: index.html');
    exit();
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize form data
$studentno = $lastname = $firstname = $middleinitial = $dob = $email = $gender = $phone = $course = $yearlevel = $block = $address = $contactname = $contactno = $relationship = "";

// Error variables
$nameError = $emailError = $studentNoError = ""; 

// Debug: Check if form data is being passed correctly
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Capture form data
    $studentno = isset($_POST['studentno']) ? $_POST['studentno'] : '';
    $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $middleinitial = isset($_POST['middleinitial']) ? $_POST['middleinitial'] : '';
    $extname = isset($_POST['extname']) ? $_POST['extname'] : '';
    $dob = isset($_POST['dob']) ? $_POST['dob'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : ''; 
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $course = isset($_POST['course']) ? $_POST['course'] : '';
    $yearlevel = isset($_POST['yearlevel']) ? $_POST['yearlevel'] : '';
    $block = isset($_POST['block']) ? $_POST['block'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $contactname = isset($_POST['contactname']) ? $_POST['contactname'] : '';
    $contactno = isset($_POST['contactno']) ? $_POST['contactno'] : '';
    $relationship = isset($_POST['relationship']) ? $_POST['relationship'] : '';

    // Validation to ensure required fields are not empty
    if (empty($studentno) || empty($lastname) || empty($firstname) || empty($dob) || empty($email) || empty($gender) || empty($phone) || empty($course) || empty($yearlevel) || empty($address) || empty($contactname) || empty($contactno) || empty($relationship)) {
        $errorMessage = "Please fill out all required fields!";
    }

    // Prepare SQL statement
    $sql = "INSERT INTO student_registration (studentno, lastname, firstname, middleinitial, extname, dob, email, gender, phone, course, yearlevel, block, address, contactname, contactno, relationship)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssssss",
        $studentno,
        $lastname,
        $firstname,
        $middleinitial,
        $extname,
        $dob,
        $email,
        $gender,
        $phone,
        $course,
        $yearlevel,
        $block,
        $address,
        $contactname,
        $contactno,
        $relationship
    );

    if ($stmt->execute()) {
        echo '
        <style>
            @keyframes fadeIn {
                from { opacity: 0; transform: translate(-50%, -60%); }
                to { opacity: 1; transform: translate(-50%, -50%); }
            }

            #successTooltip {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #ffffff;
                color: #333;
                padding: 30px 40px;
                border-radius: 16px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                font-family: "Segoe UI", sans-serif;
                text-align: center;
                max-width: 420px;
                width: 90%;
                opacity: 0;
                animation: fadeIn 0.5s ease-out forwards;
            }

            #successTooltip .icon {
                font-size: 36px;
                color: #4CAF50;
                margin-bottom: 15px;
            }

            #successTooltip h2 {
                font-size: 22px;
                margin: 0 0 10px;
                color: #2E7D32;
            }

            #successTooltip p {
                font-size: 16px;
                margin: 0;
                line-height: 1.5;
            }
        </style>

        <div id="successTooltip">
            <div class="icon">✅</div>
            <h2>Registration Successful</h2>
            <p>Please wait for your name to be called<br>on the Queue Monitor. Thank you!</p>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const tooltip = document.getElementById("successTooltip");
                if (tooltip) {
                    tooltip.style.display = "block";
                }

                setTimeout(function() {
                    window.location.href = "index.html";
                }, 5000);
            });
        </script>';
    } else {
        if ($stmt->errno === 1062) {
            echo '
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; transform: translate(-50%, -60%); }
                    to { opacity: 1; transform: translate(-50%, -50%); }
                }

                #errorTooltip {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-color: #fff3f3;
                    color: #c62828;
                    padding: 30px 40px;
                    border-radius: 16px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                    z-index: 9999;
                    font-family: "Segoe UI", sans-serif;
                    text-align: center;
                    max-width: 420px;
                    width: 90%;
                    opacity: 0;
                    animation: fadeIn 0.5s ease-out forwards;
                }

                #errorTooltip .icon {
                    font-size: 36px;
                    color: #e53935;
                    margin-bottom: 15px;
                }

                #errorTooltip h2 {
                    font-size: 22px;
                    margin: 0 0 10px;
                    color: #c62828;
                }

                #errorTooltip p {
                    font-size: 16px;
                    margin: 0;
                    line-height: 1.5;
                }
            </style>

            <div id="errorTooltip">
                <div class="icon">❌</div>
                <h2>Duplicate Entry</h2>
                <p>A student with this Student Number or Email already exists.<br>Please check your input and try again.</p>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const tooltip = document.getElementById("errorTooltip");
                    if (tooltip) {
                        tooltip.style.display = "block";
                    }

                    setTimeout(function() {
                        window.location.href = "new_student.php";
                    }, 5000);
                });
            </script>';
        } else {
            echo "<script>alert('An unexpected error occurred: " . addslashes($stmt->error) . "'); window.location.href='new_student.php';</script>";
        }
    }

    // Close connection
    $conn->close();
} else {
    // Redirect if accessed without POST
    header("Location: index.html");
    exit();
}
?>
