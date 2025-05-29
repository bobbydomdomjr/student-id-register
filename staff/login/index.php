<?php
global $conn;
session_start();
include('../../db.php');

// Initialize error message
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SQL query to fetch admin details
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if admin exists
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verify password using password_verify()
        if (password_verify($password, $row['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['admin'] = $username;
            $_SESSION['role'] = $row['role']; // Store the role from the admin table in session
            $_SESSION['welcome_message'] = "Welcome to the Staff Panel!";
            header("Location: ./../dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid username!";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login into SIDRS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

   <style>
        body {
            background: #ffff;
            height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: "Helvetica Neue", Arial, sans-serif;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
        }

        .container-box {
            background-color: white;
            border-radius: 12px;
            border: 1px solid #dee2e6;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.20);
            width: 400px;
            padding: 25px;
            text-align: center;
            margin-top: auto;
            margin-bottom: auto;
        }

        .logo-box {
            width: 100px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0px;
        }

        h5 {
            font-size: 1.6rem;
            font-weight: bold;
            color: #4267B2;
            margin-bottom: 5px;
            margin-top: 10px;
        }

        p {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .form-control {
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-custom {
            width: 100%;    
            padding: 12px;
            font-size: 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .btn-blue {
            background-color: #4267B2;
            color: white;
            border: none;
        }

        .btn-blue:hover {
            background-color: #007bb8;
        }

        footer {
            margin-top: 20px;
            font-size: 14px;
            color: #495057;
            letter-spacing: 0.5px;
        }

        .input-icon {
    position: relative;
}


.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-right: none;
    border-radius: 8px 0 0 8px;
    padding: 12px 12px;
    color:rgb(30, 111, 192);
}


.input-group-prepend,
.input-group-append {
    display: flex;
    align-items: center;  /* Ensure icons are aligned correctly */
}

.input-group .form-control:focus {
    box-shadow: none;  /* Remove box-shadow when focused for clean appearance */
    border-color: #80bdff;  /* Change border color on focus */
}

        .form-group {
            margin-bottom: 18px;
        }

        .alert-danger {
            padding: 10px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="wrapper">
    <div class="container-box">
        <!-- Logo Section -->
        <div class="logo-box">
            <img src="../../pictures/12.jpg" alt="Logo" style="width: 100%; height: auto; object-fit: contain;">
        </div>

        <!-- Login Heading -->
            <h5>Staff Login</h5><p>

            <!-- Show Error Message -->
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

 <!-- Login Form -->
<form method="POST" action="" id="loginForm">
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Enter Username" required>
        </div>
    </div>

    <div class="form-group">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password" required>
        </div>
    </div>

    <button type="submit" class="btn btn-blue btn-custom">Log In</button>

    <footer class="text-center text-muted mt-2">
        <small>&copy; 2025 Student ID Registration System | Bobby Domdom Jr</small>
    </footer>
</form>
    </div>

    <!-- Footer Section -->
</div>

<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<script>
    $(document).ready(function () {
        // Toggle password visibility
        $("#togglePassword").click(function () {
            const passwordField = $("#password");
            const type = passwordField.attr("type") === "password" ? "text" : "password";
            passwordField.attr("type", type);
            $(this).toggleClass("fa-eye fa-eye-slash");
        });
    });
</script>

</body>

</html>
