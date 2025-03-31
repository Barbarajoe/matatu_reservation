<?php
// filepath: c:\xampp\htdocs\matatu_reservation\book_ticket.php
session_start();
include 'config.php'; // Ensure this path is correct

if (!isset($conn)) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: passenger_home.html");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in");
}

if (!isset($_POST['route'], $_POST['seat'], $_POST['payment_method'])) {
    die("Error: Missing required form fields");
}

$user_id = $_SESSION['user_id'];
$route = $_POST['route'];
$seat = $_POST['seat'];
$payment_method = $_POST['payment_method'];

try {
    $sql = "INSERT INTO bookings (user_id, route, seat, payment_method) VALUES (:user_id, :route, :seat, :payment_method)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':route', $route);
    $stmt->bindParam(':seat', $seat);
    $stmt->bindParam(':payment_method', $payment_method);
    
    if ($stmt->execute()) {
        header("Location: passenger_home.html?booking_success=1");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2];
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>