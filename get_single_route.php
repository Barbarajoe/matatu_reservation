<?php
require 'config.php';

header('Content-Type: application/json');

// Debugging: Log received data
$data = json_decode(file_get_contents('php://input'), true);
error_log('Received data: ' . print_r($data, true));

// Validate required fields
if (!isset($data['routeName'], $data['departurePoint'], $data['arrivalPoint'], $data['price'], $data['vehicle_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

// Validate price is numeric and positive
if (!is_numeric($data['price']) || $data['price'] <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Price must be a positive number'
    ]);
    exit;
}

// Check if vehicle exists
try {
    $stmtCheckVehicle = $pdo->prepare("SELECT 1 FROM vehicles WHERE vehicle_id = ?");
    $stmtCheckVehicle->execute([$data['vehicleId']]);
    if (!$stmtCheckVehicle->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid vehicle ID'
        ]);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Vehicle validation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to validate vehicle'
    ]);
    exit;
}

// Insert route with transaction
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO routes 
        (route_name, departure_point, arrival_point, price, vehicle_id) 
        VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['routeName'],
        $data['departurePoint'],
        $data['arrivalPoint'],
        $data['price'],
        $data['vehicleId']
    ]);
    
    $routeId = $pdo->lastInsertId();
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Route added successfully',
        'routeId' => $routeId
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add route: ' . $e->getMessage()
    ]);
}
?>