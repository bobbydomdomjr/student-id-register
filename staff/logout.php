<?php
session_start();
session_destroy(); // Destroy all sessions
header("Location: ./login/index.php"); // Redirect to login
exit();
?>
