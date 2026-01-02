<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_USERNAME', 'store_user');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'simple_store');

// Create a database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");
?>