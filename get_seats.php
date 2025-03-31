<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Validate and sanitize input
    $route_id = filter_input(INPUT_GET, 'route_id', FILTER_VALIDATE_INT);
    
    if (!$route_id) {
        throw new Exception("Invalid route ID");
    }

    // Get total seats for this vehicle type
    $stmt = $conn->prepare("
        SELECT v.seat_capacity, r.vehicle_id 
        FROM routes r
        JOIN vehicles v ON r.vehicle_id = v.id
        WHERE r.id = ?
    ");
    $stmt->execute([$route_id]);
    $route_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$route_info) {
        throw new Exception("Route not found");
    }

    // Get booked seats for this route
    $stmt = $conn->prepare("
        SELECT seat_numbers 
        FROM bookings 
        WHERE route_id = ? 
        AND status IN ('confirmed', 'pending')
    ");
    $stmt->execute([$route_id]);
    $booked_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Generate all possible seats
    $all_seats = generate_seat_map($route_info['seat_capacity']);
    $taken_seats = [];

    // Process booked seats
    foreach ($booked_seats as $seats) {
        if ($seats) {
            $taken_seats = array_merge($taken_seats, explode(',', $seats));
        }
    }

    // Get available seats
    $available_seats = array_diff($all_seats, $taken_seats);

    echo json_encode([
        'status' => 'success',
        'available_seats' => array_values($available_seats),
        'seat_map' => $all_seats
    ]);

} catch (Exception $e) {
    error_log("Seat error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch seat availability'
    ]);
}

/**
 * Generates seat map based on vehicle capacity
 */
function generate_seat_map($capacity) {
    $rows = ceil($capacity / 4);
    $seats = [];
    
    for ($row = 1; $row <= $rows; $row++) {
        foreach (['A', 'B', 'C', 'D'] as $seat) {
            $seats[] = $row . $seat;
            if (count($seats) >= $capacity) break;
        }
    }
    
    return $seats;
}
