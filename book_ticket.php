<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = (int)$_POST['route_id'];
    $selected_seats = explode(',', $_POST['seat_numbers']); // e.g., '2A,2B'
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Fetch route details to calculate total amount
        $stmt = $pdo->prepare("SELECT price, vehicle_id FROM routes WHERE route_id = ?");
        $stmt->execute([$route_id]);
        $route = $stmt->fetch();
        if (!$route) {
            throw new Exception('Invalid route');
        }

        $price_per_seat = $route['price'];
        $vehicle_id = $route['vehicle_id'];
        $total_amount = $price_per_seat * count($selected_seats);

        // Check if selected seats are available
        $placeholders = implode(',', array_fill(0, count($selected_seats), '?'));
        $stmt = $pdo->prepare("SELECT seat_number FROM seats WHERE vehicle_id = ? AND seat_number IN ($placeholders) AND is_available = TRUE FOR UPDATE");
        $stmt->execute(array_merge([$vehicle_id], $selected_seats));
        $available_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($available_seats) !== count($selected_seats)) {
            throw new Exception('One or more selected seats are not available');
        }

        // Create booking
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, route_id, seat_numbers, total_amount, booking_date, status, payment_method) VALUES (?, ?, ?, ?, NOW(), 'pending', 'mpesa')");
        $stmt->execute([$user_id, $route_id, implode(',', $selected_seats), $total_amount]);
        $booking_id = $pdo->lastInsertId();

        // Update seats
        $stmt = $pdo->prepare("UPDATE seats SET is_available = FALSE, booking_id = ? WHERE vehicle_id = ? AND seat_number IN ($placeholders)");
        $stmt->execute(array_merge([$booking_id, $vehicle_id], $selected_seats));

        $pdo->commit();
        header("Location: View_Booking.html?success=Booking created successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: booktickets.php?error=" . urlencode($e->getMessage()));
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket</title>
</head>
<body>
    <h2>Book a Ticket</h2>
    <form method="POST">
        <label>Route ID: <input type="number" name="route_id" value="1" required></label><br>
        <label>Seats (comma-separated, e.g., 2A,2B): <input type="text" name="seat_numbers" required></label><br>
        <button type="submit">Book</button>
    </form>
</body>
</html>