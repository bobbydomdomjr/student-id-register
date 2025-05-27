<?php
require_once __DIR__.'/db/env_parser.php';

// load the .env file
loadEnv(__DIR__.'/.env');

// ————————————————————————————————
// 2) Read from getenv() with fallbacks
$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname   = getenv('DB_NAME')     ?: 'sti-mis_db';
$port     = getenv('DB_PORT')     ?: '3306';

// Connect
$conn = new mysqli($host, $user, $password, $dbname, (int)$port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
