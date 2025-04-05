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
    echo json_encode(['success' => false, 'message' => 'Forbidden: Only operators or admins can create routes']);
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
$required_fields = ['routeName', 'departurePoint', 'arrivalPoint', 'departureTime', 'price', 'vehicleId'];
foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Validate data types
$route_name = trim($data['routeName']);
$departure_point = trim($data['departurePoint']);
$arrival_point = trim($data['arrivalPoint']);
$departure_time = $data['departureTime'];
$price = (float)$data['price'];
$vehicle_id = (int)$data['vehicleId'];
$departure_coords = $data['departureCoords'] ?? null; // Optional
$arrival_coords = $data['arrivalCoords'] ?? null; // Optional

// Additional validations
if (empty($route_name) || empty($departure_point) || empty($arrival_point)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Route name, departure point, and arrival point cannot be empty']);
    exit;
}

if ($price <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Price must be a positive number']);
    exit;
}

// Validate departure_time format (e.g., '2025-04-10 08:00:00')
if (!DateTime::createFromFormat('Y-m-d H:i:s', $departure_time)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid departure time format. Use YYYY-MM-DD HH:MM:SS']);
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
    // Build the insert query dynamically based on provided fields
    $fields = ['route_name', 'departure_point', 'arrival_point', 'departure_time', 'price', 'vehicle_id'];
    $placeholders = ['?', '?', '?', '?', '?', '?'];
    $params = [$route_name, $departure_point, $arrival_point, $departure_time, $price, $vehicle_id];

    if ($departure_coords) {
        $fields[] = 'departure_coords';
        $placeholders[] = 'ST_GeomFromText(?)';
        $params[] = $departure_coords;
    }
    if ($arrival_coords) {
        $fields[] = 'arrival_coords';
        $placeholders[] = 'ST_GeomFromText(?)';
        $params[] = $arrival_coords;
    }

    $query = "INSERT INTO routes (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);

    if ($success) {
        $route_id = $pdo->lastInsertId();
        auditLog('ROUTE_CREATE', "Route ID $route_id created by user {$_SESSION['user_id']}");
        echo json_encode(['success' => true, 'message' => 'Route created successfully', 'routeId' => $route_id]);
    } else {
        throw new Exception('Failed to create route');
    }
} catch (PDOException $e) {
    http_response_code(500);
    auditLog('ROUTE_CREATE_ERROR', "Error creating route: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    auditLog('ROUTE_CREATE_ERROR', "Error creating route: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}