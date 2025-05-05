<?php
require '../db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students.csv');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, ['Student No', 'Full Name', 'Course', 'Year', 'Block', 'Email', 'Registered']);

// Query to fetch data
$query = "SELECT * FROM student_registration ORDER BY registration_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fullname = $row['firstname'] . ' ' . $row['middleinitial'] . ' ' . $row['lastname'];
        fputcsv($output, [
            $row['studentno'],
            $fullname,
            $row['course'],
            $row['yearlevel'],
            $row['block'],
            $row['email'],
            $row['registration_date']
        ]);
    }
} else {
    fputcsv($output, ['No data available']);
}

fclose($output);
exit;
?>
