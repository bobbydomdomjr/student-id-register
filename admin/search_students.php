<?php
// Include the database connection
include('../db.php');

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Query to search for students based on the search term
$sql = "SELECT * FROM student_registration WHERE CONCAT(firstname, ' ', lastname, ' ', course) LIKE ?";

$stmt = $conn->prepare($sql);
$searchTermLike = '%' . $searchTerm . '%';
$stmt->bind_param("s", $searchTermLike);

$stmt->execute();
$result = $stmt->get_result();

// Create an array to hold the students
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Return the result as JSON
echo json_encode(['students' => $students]);

$stmt->close();
$conn->close();
?>
