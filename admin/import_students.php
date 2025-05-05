<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');

    if ($handle === false) {
        $_SESSION['error_message'] = 'Failed to open the file.';
        header('Location: students.php');
        exit();
    }

    // Skip the first row (header)
    fgetcsv($handle);

    $imported = 0;
    $errors = 0;

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if (count($data) < 7) {
            $errors++;
            continue;
        }

        list($studentno, $firstname, $middleinitial, $lastname, $extname, $contactname, $contactno) = $data;
        $registration_date = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO student_registration (studentno, firstname, middleinitial, lastname, extname, contactname, contactno, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $studentno, $firstname, $middleinitial, $lastname, $extname, $contactname, $contactno, $registration_date);

        if ($stmt->execute()) {
            $imported++;
        } else {
            $errors++;
        }
        $stmt->close();
    }

    fclose($handle);

    $_SESSION['success_message'] = "$imported student(s) imported. $errors error(s).";
    header('Location: students.php');
    exit();
} else {
    $_SESSION['error_message'] = 'Please upload a valid CSV file.';
    header('Location: students.php');
    exit();
}
