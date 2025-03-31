<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized access']));
}

// Validate input
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Booking ID is required']));



// Sanitize input function
function sanitizeInput($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

$bookingId = sanitizeInput($_POST['booking_id']);

    try {
        // Verify ownership
        $stmt = $conn->prepare("
            SELECT user_id 
            FROM bookings 
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            http_response_code(404);
            die(json_encode(['error' => 'Booking not found']));
        }
        
        if (!$booking || ($booking['user_id'] !== $_SESSION['user_id'] && $_SESSION['role'] !== 'administrator')) {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized action']));
        }

        $stmt = $conn->prepare("
            UPDATE bookings 
            SET status = 'cancelled'
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);

        echo json_encode(['success' => 'Booking cancelled']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>