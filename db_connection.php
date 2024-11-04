<?php
$servername = "localhost"; // Usually 'localhost' for XAMPP
$username = "root"; // Default MySQL username for XAMPP
$password = ""; // Default MySQL password for XAMPP (blank)
$dbname = "enterprise"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the MySQL time zone to Malaysia time
if (!$conn->query("SET time_zone = '+08:00'")) {
    die("Failed to set time zone: " . $conn->error);
}

// Optional: Set default timezone for PHP script execution
date_default_timezone_set('Asia/Kuala_Lumpur');

?>

