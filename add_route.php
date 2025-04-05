<?php
require 'config.php'; // Use your PDO connection
session_start();

header('Content-Type: application/json');

// Authentication and role check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in']);
    exit;
}

$user_role = $_SESSION['role'] ?? '';
if (!in_array($user_role, ['operator', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Only operators or admins can update routes']);
    exit;
}

// Audit log function
function auditLog($action_type, $action_details) {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'];

    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action_type, action_details, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action_type, $action_details, $ip_address]);
    } catch (PDOException $e) {
        error_log("Audit Log DB Error: " . $e->getMessage());
    }
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['routeId', 'routeName', 'departurePoint', 'arrivalPoint', 'price', 'vehicleId'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Validate data types
$route_id = (int)$data['routeId'];
$route_name = trim($data['routeName']);
$departure_point = trim($data['departurePoint']);
$arrival_point = trim($data['arrivalPoint']);
$price = (float)$data['price'];
$vehicle_id = (int)$data['vehicleId'];
$departure_time = $data['departureTime'] ?? null; // Optional
$departure_coords = $data['departureCoords'] ?? null; // Optional
$arrival_coords = $data['arrivalCoords'] ?? null; // Optional

if ($price <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Price must be a positive number']);
    exit;
}

// Validate vehicle_id exists
$stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE vehicle_id = ?");
$stmt->execute([$vehicle_id]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid vehicle ID']);
    exit;
}

// Validate route_id exists
$stmt = $pdo->prepare("SELECT route_id FROM routes WHERE route_id = ?");
$stmt->execute([$route_id]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid route ID']);
    exit;
}

// Validate coordinates if provided
if ($departure_coords && !preg_match('/^POINT\(-?\d+(\.\d+)?\s-?\d+(\.\d+)?\)$/', $departure_coords)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid departure coordinates format']);
    exit;
}
if ($arrival_coords && !preg_match('/^POINT\(-?\d+(\.\d+)?\s-?\d+(\.\d+)?\)$/', $arrival_coords)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid arrival coordinates format']);
    exit;
}

try {
    // Build the update query dynamically based on provided fields
    $fields = [
        'route_name = ?',
        'departure_point = ?',
        'arrival_point = ?',
        'price = ?',
        'vehicle_id = ?'
    ];
    $params = [
        $route_name,
        $departure_point,
        $arrival_point,
        $price,
        $vehicle_id
    ];

    if ($departure_time) {
        $fields[] = 'departure_time = ?';
        $params[] = $departure_time;
    }
    if ($departure_coords) {
        $fields[] = 'departure_coords = ST_GeomFromText(?)';
        $params[] = $departure_coords;
    }
    if ($arrival_coords) {
        $fields[] = 'arrival_coords = ST_GeomFromText(?)';
        $params[] = $arrival_coords;
    }

    $params[] = $route_id; // For WHERE clause

    $query = "UPDATE routes SET " . implode(', ', $fields) . " WHERE route_id = ?";
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);

    if ($success && $stmt->rowCount() > 0) {
        auditLog('ROUTE_UPDATE', "Route ID $route_id updated by user {$_SESSION['user_id']}");
        echo json_encode(['success' => true, 'message' => 'Route updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made to the route']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    auditLog('ROUTE_UPDATE_ERROR', "Error updating route ID $route_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    auditLog('ROUTE_UPDATE_ERROR', "Error updating route ID $route_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}