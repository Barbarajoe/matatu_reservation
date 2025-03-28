<?php
include 'api/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure all expected POST values are set
    if (isset($_POST['username'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['role'])) {
        // Trim inputs for extra safety
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $role = trim($_POST['role']);
        // Hash the password securely
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role) VALUES (:username, :email, :phone, :password, :role)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role', $role);

            // Execute the query and redirect if successful
            if ($stmt->execute()) {
                header("Location: login.html?registered=1");
                exit();
            } else {
                // Display detailed error message if execution fails
                $errorInfo = $stmt->errorInfo();
                echo "Error: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            // Catch and display any PDO exceptions
            echo "Database error: " . $e->getMessage();
        }
    } else {
        // Notify if any field is missing
        echo "All fields are required.";
    }
}
?>
