<?php
header('Content-Type: text/plain');
require 'config.php';

// Get incoming USSD data (e.g., from Africa's Talking)
$sessionId = $_POST['sessionId'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$text = trim($_POST['text'] ?? '');

// Split user input into steps
$input = $text ? explode('*', $text) : [];
$last_input = end($input) ?: '';

// Check or start session
$stmt = $pdo->prepare("SELECT * FROM ussd_sessions WHERE session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    $stmt = $pdo->prepare("INSERT INTO ussd_sessions (session_id, user_phone, current_step, session_data) VALUES (?, ?, 'menu', ?)");
    $stmt->execute([$sessionId, $phoneNumber, json_encode([])]);
    $response = "CON Welcome to Matatu Booking\n1. Book Seat\n2. Check Bookings\n3. Exit";
} else {
    $step = $session['current_step'];
    $session_data = json_decode($session['session_data'], true) ?: [];

    switch ($step) {
        case 'menu':
            if ($last_input == '1') {
                $response = "CON Enter Route ID (e.g., 1):";
                $new_step = 'route';
            } elseif ($last_input == '2') {
                $response = "CON Enter User ID:";
                $new_step = 'check_bookings';
            } elseif ($last_input == '3') {
                $response = "END Goodbye!";
                $new_step = 'end';
            } else {
                $response = "CON Invalid option.\n1. Book Seat\n2. Check Bookings\n3. Exit";
                $new_step = 'menu';
            }
            break;

        case 'route':
            $session_data['route_id'] = $last_input;
            $stmt = $pdo->prepare("SELECT route_name, price FROM routes WHERE route_id = ?");
            $stmt->execute([$last_input]);
            $route = $stmt->fetch();
            if ($route) {
                $response = "CON Route: {$route['route_name']} (KES {$route['price']})\nEnter Seat (e.g., 1A):";
                $new_step = 'seat';
            } else {
                $response = "CON Invalid Route ID. Try again:";
                $new_step = 'route';
            }
            break;

        case 'seat':
            $session_data['seat'] = $last_input;
            $stmt = $pdo->prepare("SELECT is_booked FROM seats WHERE route_id = ? AND seat_number = ?");
            $stmt->execute([$session_data['route_id'], $last_input]);
            $seat_status = $stmt->fetchColumn();
            if ($seat_status === false) { // Seat exists and is not booked
                $response = "CON Confirm Booking?\n1. Yes\n2. No";
                $new_step = 'confirm';
            } else {
                $response = "CON Seat $last_input is unavailable. Try another:";
                $new_step = 'seat';
            }
            break;

        case 'confirm':
            if ($last_input == '1') {
                // Map phone to user (you’d need a users table lookup or registration step)
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?"); // Example; adjust based on your logic
                $stmt->execute([$phoneNumber]); // Assuming email holds phone for now
                $user_id = $stmt->fetchColumn() ?: 1; // Default to 1 if not found

                $route_id = $session_data['route_id'];
                $seat = $session_data['seat'];
                $stmt = $pdo->prepare("SELECT price FROM routes WHERE route_id = ?");
                $stmt->execute([$route_id]);
                $price = $stmt->fetchColumn();

                // Create booking
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, route_id, seat_numbers, total_amount, status, payment_method) VALUES (?, ?, ?, ?, 'pending', 'mpesa')");
                $stmt->execute([$user_id, $route_id, $seat, $price]);
                $booking_id = $pdo->lastInsertId();

                // Mark seat as booked
                $stmt = $pdo->prepare("UPDATE seats SET is_booked = TRUE WHERE route_id = ? AND seat_number = ?");
                $stmt->execute([$route_id, $seat]);

                $response = "END Booking Created! ID: $booking_id\nPay KES $price to MPesa: 123456";
                $new_step = 'end';
            } else {
                $response = "END Booking Cancelled.";
                $new_step = 'end';
            }
            break;

        case 'check_bookings':
            $user_id = (int)$last_input;
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $bookings = $stmt->fetchAll();
            $response = "END Your Bookings:\n";
            foreach ($bookings as $b) {
                $response .= "ID: {$b['booking_id']}, Seat: {$b['seat_numbers']}, Status: {$b['status']}\n";
            }
            $new_step = 'end';
            break;

        default:
            $response = "END Session expired.";
            $new_step = 'end';
            break;
    }

    // Update session
    $stmt = $pdo->prepare("UPDATE ussd_sessions SET current_step = ?, session_data = ? WHERE session_id = ?");
    $stmt->execute([$new_step, json_encode($session_data), $sessionId]);
}

echo $response;