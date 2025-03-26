<?php
$servername = "localhost"; // or your MySQL host (e.g., 'localhost', '127.0.0.1', or a remote server address)
$username = "root"; // your MySQL username
$password = ""; // your MySQL password (default is empty for root)
$dbname = "matatu_reservation"; // the name of the database you want to connect to

// Create connection
$conn = new mysqli(hostname: $servername, username: $username, password: $password, database: $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";
?>
