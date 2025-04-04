<?php
require 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['routeId'], $data['routeName'], $data['departurePoint'], $data['arrivalPoint'], $data['price'], $data['vehicleId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE routes SET   
        route_name = ?,
        departure_point = ?,
        arrival_point = ?,
        price = ?,
        vehicle_id = ?
        WHERE route_id = ?");
    
    $success = $stmt->execute([
        $data['routeName'],
        $data['departurePoint'],
        $data['arrivalPoint'],
        $data['price'],
        $data['vehicleId'],
        $data['routeId']
    ]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Route updated successfully']);
    } else {
        throw new Exception('Failed to update route');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>