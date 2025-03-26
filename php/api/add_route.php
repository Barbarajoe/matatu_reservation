<?php
require 'api/config.php';

// Verify user authentication and authorization
if (!isset($_SESSION['user_id'])) {
    header("Location: matatu_reservation/login.html?error=unauthorized");
    exit();
}

// Only allow operators and administrators
if ($_SESSION['role'] !== 'operator' && $_SESSION['role'] !== 'administrator') {
    header("Location: unauthorized.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate inputs
        $route_name = sanitizeInput($_POST['route_name']);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Validate inputs
        if (empty($route_name)) {
            throw new Exception("Route name is required");
        }

        if (!is_numeric($price) || $price <= 0) {
            throw new Exception("Invalid price value");
        }

        // Insert into database using prepared statement
        $stmt = $conn->prepare("
            INSERT INTO routes (name, price, created_by)
            VALUES (:name, :price, :created_by)
        ");

        $stmt->execute([
            ':name' => $route_name,
            ':price' => $price,
            ':created_by' => $_SESSION['user_id']
        ]);

        // Check if insertion was successful
        if ($stmt->rowCount() > 0) {
            header("Location: manage_routes.html?success=Route added successfully");
        } else {
            throw new Exception("Failed to add route");
        }

    } catch (PDOException $e) {
        // Handle database errors
        $errorMessage = $e->getCode() == 23000 
            ? "Route already exists" 
            : "Database error: " . $e->getMessage();
        header("Location: manage_routes.html?error=" . urlencode($errorMessage));
        
    } catch (Exception $e) {
        // Handle validation errors
        header("Location: manage_routes.html?error=" . urlencode($e->getMessage()));
    }
} else {
    // Redirect if accessed directly
    header("Location: manage_routes.html");
}

exit();
?>