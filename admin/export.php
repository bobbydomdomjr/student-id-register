<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ocsergs_db';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from student_registration table
$sql = "SELECT studentno, lastname, firstname, middleinitial, dob, email, gender, phone, 
               course, yearlevel, block, address, contactname, contactno, relationship 
        FROM student_registration";
$result = $conn->query($sql);

// Check if query failed
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Check if data is available
if ($result->num_rows > 0) {
    // File name and header for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="student_records.csv"');

    // Create a file pointer
    $output = fopen('php://output', 'w');

    // Add CSV column headers
    fputcsv($output, [
        'Student No', 'Last Name', 'First Name', 'Middle Initial', 'Date of Birth', 
        'Email', 'Gender', 'Phone', 'Course/Strand', 'Year Level', 'Block', 
        'Address', 'Emergency Contact Name', 'Emergency Contact No.', 'Relationship'
    ]);

    // Fetch and write data row by row
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['studentno'], $row['lastname'], $row['firstname'], $row['middleinitial'],
            $row['dob'], $row['email'], $row['gender'], $row['phone'], 
            $row['course'], $row['yearlevel'], $row['block'], $row['address'],
            $row['contactname'], $row['contactno'], $row['relationship']
        ]);
    }

    // Close file pointer
    fclose($output);
} else {
    echo "No records found.";
}

// Close database connection
$conn->close();
?>
