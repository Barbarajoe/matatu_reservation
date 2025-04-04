<?php
session_start();
require 'config.php';
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: booktickets.html");
    exit();
}

try {
    $booking_id = (int)$_POST['booking_id'];
    $payment_method = isset($_POST['mpesa_phone']) ? 'mpesa' : 'card';

    if ($payment_method === 'mpesa') {
        // Process M-Pesa payment
        $phone = preg_replace('/[^0-9]/', '', $_POST['mpesa_phone']);
        if (strlen($phone) !== 12 || !preg_match('/^254/', $phone)) {
            throw new Exception("Invalid M-Pesa number format");
        }
        
        // Here you would integrate with M-Pesa API
        // This is just a simulation
        $success = true; // Replace with actual API call
        
        if (!$success) {
            throw new Exception("M-Pesa payment failed");
        }
    } else {
        // Process card payment
        \Stripe\Stripe::setApiKey('your_stripe_secret_key');
        $payment = \Stripe\PaymentIntent::create([
            'amount' => calculateBookingAmount($booking_id),
            'currency' => 'kes',
            'payment_method' => $_POST['payment_method_id'],
            'confirm' => true,
            'return_url' => 'https://yourdomain.cm/booking_confirmation.php'
        ]);
    }
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed', payment_method = ? WHERE id = ?");
    $stmt->execute([$payment_method, $booking_id]);
    
    header("Location: booking_confirmation.php?id=$booking_id");
    exit();

} catch (Exception $e) {
    error_log("Payment error: " . $e->getMessage());
    header("Location: paymentmethod.php?booking_id=$booking_id&error=1");
    exit();
}

function calculateBookingAmount($booking_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT total_amount FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)($result['total_amount'] * 100) : 0; // Convert to cents
}
?>