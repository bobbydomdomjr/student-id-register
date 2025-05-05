<?php
include('../db.php');

// Set new admin password
$new_password = password_hash('yourpassword123', PASSWORD_DEFAULT);

// Prepare SQL query
$sql = "UPDATE admin SET password = ? WHERE username = 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $new_password);

// Execute and check if successful
if ($stmt->execute()) {
    echo "✅ Password updated successfully!";
} else {
    echo "❌ Error updating password: " . $conn->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
