<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

/**
 * Calculate the booking amount based on the booking ID.
 *
 * @param int $booking_id The ID of the booking.
 * @return int The calculated amount in the smallest currency unit (e.g., cents).
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}
 function calculateBookingAmount($booking_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT amount FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['amount'] : 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            throw new Exception("CSRF validation failed");
        }
        
        $booking_id = sanitizeInput($_POST['booking_id']);
        $payment_method = isset($_POST['mpesa_phone']) ? 'mpesa' : 'card';

        if ($payment_method === 'mpesa') {
            // Process M-Pesa payment
            $phone = sanitizeInput($_POST['mpesa_phone']);
            // Initiate M-Pesa STK push
        } else {
            // Process card payment
            \Stripe\Stripe::setApiKey('pk_test_51R8dCP4CMG9dsCYOxlurqTUU5XdiHFFH8OwLd9P9EnAD6mYxcaduQhZ09zlzyaSWtwroFxcF5EUT4Z9nHmGTVNOQ00GI0SGyMh');
            $payment = \Stripe\PaymentIntent::create([
                'amount' => calculateBookingAmount($booking_id),
                'currency' => 'kes',
                'payment_method' => $_POST['payment_method_id'],
                'confirm' => true
            ]);
        }
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        header("Location: booking_confirmation.php?id=$booking_id");
    } catch (Exception $e) {
        error_log("Payment error: " . $e->getMessage());
        header("Location: paymentmethod.html?booking_id=$booking_id&error=1");
    }
    exit();
}
?>