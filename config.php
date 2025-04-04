<?php
// config.php
define('STRIPE_SECRET_KEY', 'pk_test_51R8dCP4CMG9dsCYOxlurqTUU5XdiHFFH8OwLd9P9EnAD6mYxcaduQhZ09zlzyaSWtwroFxcF5EUT4Z9nHmGTVNOQ00GI0SGyMh');  // Get from Stripe Dashboard
// Email Configuration
define('EMAIL_HOST', 'smtp.yourprovider.com');
define('EMAIL_USER', 'your@email.com');
define('EMAIL_PASS', 'yourpassword');
define('EMAIL_FROM', 'noreply@yourdomain.com');

// Map Configuration
define('MAPBOX_API_KEY', 'your_mapbox_key'); // Optional for enhanced maps
$host = 'localhost';
$db   = 'matatu_reservation';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exceptions
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
