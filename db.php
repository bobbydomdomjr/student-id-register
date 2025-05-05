<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = ''; // Default XAMPP password is empty
$dbname = 'sti-mis_db';


// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');

// Function to execute query and handle errors
function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        die("Query Error: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row ? reset($row) : 0; // Returns the first column value or 0
}

?>
