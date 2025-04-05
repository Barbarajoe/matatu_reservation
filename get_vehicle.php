<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $stmt = $connect->query("SELECT vehicle_id, registration, seat_capacity FROM vehicles");
    $vehicles = [];
    while ($row = $stmt->fetch_assoc()) {
        $vehicles[] = $row;
    }
    echo json_encode($vehicles);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>