<?php
<<<<<<< HEAD
// filepath: c:\xampp\htdocs\matatu_reservation\book_ticket.php

use Stripe\Billing\Alert;

=======
header('Content-Type: application/json');
>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
session_start();
require 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!$data || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

<<<<<<< HEAD
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

=======
>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
try {
    // Calculate price based on route
    $routePrices = [
        'nairobi-mombasa' => 1500,
        'nairobi-kisumu' => 1200,
        'nairobi-eldoret' => 800,
        'mombasa-nairobi' => 1500,
        'kisumu-nairobi' => 1200,
        'eldoret-nairobi' => 800
    ];
    
<<<<<<< HEAD
    if ($stmt->execute()) {
        header("Location: passenger_home.html?booking_success=1");
        Alert::success("Booking successful! Your ticket has been reserved.");
        exit();
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2];
=======
    // Convert route name to route ID (you'll need to implement this)
    $routeNameToId = [
        'nairobi-mombasa' => 1,
        'nairobi-kisumu' => 2,
        'nairobi-eldoret' => 3,
        'mombasa-nairobi' => 4,
        'kisumu-nairobi' => 5,
        'eldoret-nairobi' => 6
    ];
    
    $routeId = $routeNameToId[$data['route']] ?? null;
    if (!$routeId) {
        throw new Exception("Invalid route selected");
>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
    }
    
    $pricePerTicket = $routePrices[$data['route']] ?? 500;
    $seatCount = count(explode(',', $data['seats']));
    $totalAmount = $pricePerTicket * $seatCount; // Removed ticket_count since your table doesn't have it

    // Insert into bookings table (matches your actual structure)
    $stmt = $conn->prepare("INSERT INTO bookings 
        (user_id, route_id, seat_numbers, total_amount, status) 
        VALUES (?, ?, ?, ?, 'pending')");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $routeId,
        $data['seats'],
        $totalAmount
    ]);

    $bookingId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'message' => 'Booking created successfully'
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'error_info' => isset($conn) ? $conn->errorInfo() : null
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>