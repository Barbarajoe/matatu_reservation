<?php
require 'config.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $booking_id = sanitizeInput($_POST['booking_id']);
    $amount = sanitizeInput($_POST['amount']);
    $method = sanitizeInput($_POST['method']);

    try {
        $conn->beginTransaction();
        
        // Process payment
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, method) VALUES (?, ?, ?)");
        $stmt->execute([$booking_id, $amount, $method]);
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'paid' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        $conn->commit();
        header("Location: paymentmethod.html?success=1");
    } catch(Exception $e) {
        $conn->rollBack();
        header("Location: paymentmethod.html?error=".urlencode($e->getMessage()));
    }
}
?>