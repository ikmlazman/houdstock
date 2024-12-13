<?php
$host = "168.138.180.170"; // Your database host
$user = "ikmal";      // Your database username
$password = "ikmal2024";      // Your database password
$database = "webapplication";  // Your database name

// Create connection
$con = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
