<?php
header('Content-Type: application/json');
require 'config.php';

try {
    // Query to fetch routes and associated vehicle registrations
    $query = "
        SELECT r.route_id, r.route_name, r.departure_point, r.arrival_point, r.price, v.registration 
        FROM routes r
        LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
    ";
    $stmt = $connect->query($query);
    $routes = [];
    while ($row = $stmt->fetch_assoc()) {
        $routes[] = $row;
    }

    // Return the routes as JSON
    echo json_encode($routes);
} catch (PDOException $e) {
    // Return a 500 error with the exception message
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>