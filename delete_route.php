<?php
require 'connectiondb.php';

header('Content-Type: application/json');
$routeId = $_GET['id'] ?? null;

if (!$routeId) {
    echo json_encode(['success' => false, 'message' => 'Route ID is required']);
    exit;
}


$stmt = $pdo->prepare("DELETE FROM routes WHERE route_id = ?");

try {
    $stmt->execute([$routeId]);
    echo json_encode(['success' => true, 'message' => 'Route deleted successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>