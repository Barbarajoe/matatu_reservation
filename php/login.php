<?php
include 'api/config.php';

if (session_status() == PHP_SESSION_NONE) {
session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

try {
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['role'] = $result['role'];
        
        switch($result['role']) {
            case 'administrator':
                header("Location: administrator_home.html");
                break;
            case 'operator':
                header("Location: operator_home.html");
                break;
            default:
                header("Location: passenger_home.html");
        }
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
}
?>