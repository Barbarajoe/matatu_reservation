<?php
$servername = "localhost";  // Change to your database host (e.g., 127.0.0.1)
$username = "root";         // Your database username
$password = "";             // Your database password (leave empty if none)
$database = "matatu_reservation";  // Change to your actual database name

// Create connection
$connect = mysqli_connect('localhost', 'root', "", 'matatu_reservation');

// Check connection
if (!$connect) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
