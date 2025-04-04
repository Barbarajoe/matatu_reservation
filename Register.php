<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Creates a PDO instance ($conn)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'], $_POST['email'], $_POST['phone'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password) VALUES (:username, :email, :phone, :password)");
            if (!$stmt) {
                $errorInfo = $conn->errorInfo();
                die("Prepare error: " . $errorInfo[2]);
            }

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $password);

            if ($stmt->execute()) {
                header("Location: login.html?registered=1");
                exit();
            } else {
                $errorInfo = $stmt->errorInfo();
                echo "Error executing query: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    } else {
        echo "All fields are required.";
    }
} else {
    echo "Invalid request method.";
}
?>