<?php
$host = 'localhost';
$dbname = 'matatu_reservation';
$username = 'root';
$password = ''; // Leave empty if no password is set for root

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>