<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matatu_reservation";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}