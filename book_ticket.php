<?php
// filepath: c:\xampp\htdocs\matatu_reservation\book_ticket.php

use Stripe\Billing\Alert;

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session
    $route = $_POST['route'];
    $date = $_POST['date'];
    $seat = $_POST['seat'];
    $ticketCount = $_POST['ticket_count'];

try {
    $sql = "INSERT INTO bookings (user_id, route, seat, payment_method) VALUES (:user_id, :route, :seat, :payment_method)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':route', $route);
    $stmt->bindParam(':seat', $seat);
    $stmt->bindParam(':payment_method', $payment_method);
    
    if ($stmt->execute()) {
        header("Location: passenger_home.html?booking_success=1");
        Alert::success("Booking successful! Your ticket has been reserved.");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2];
    }
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>