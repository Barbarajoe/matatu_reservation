<?php
$host = 'localhost';
$dbname = 'matatu_reservation';
$username = 'root';
$password = ''; // Leave empty if no password is set for root

try {
<<<<<<< HEAD
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exceptions
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
=======
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
