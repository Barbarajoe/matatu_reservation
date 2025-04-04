<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include 'config.php'; // Ensure this initializes $conn as a PDO instance
// After including config.php, check if $conn is set
if (!isset($conn)) {
    die("Database connection not established.");
}
<<<<<<< HEAD
include "config.php"; // Include the database configuration file
include('navbar.php');
=======

// Rest of your code...
$message = ""; // Variable to store feedback messages

>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        try {
            // Query the database for the user - use two distinct parameters
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $username); // Bind the same value to second parameter
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verify the password against the stored hash
                if (password_verify($password, $user['password_hash'])) {
                    // Password is correct, start session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['Role'];
                    
                    // Redirect to appropriate page based on role
                    switch (strtolower($user['Role'])) { // Make role comparison case-insensitive
                        case 'admin':
                            header("Location: admininistrator_home.html");
                            break;
                        case 'operator':
                            header("Location: operator_home.html");
                            break;
                        default:
                            header("Location: passenger_home.html");
                    }
                    exit();
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "User not found.";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 400px;
            background: #d3d3d3;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: #ff6600;
        }
        input, button {
            width: 90%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #ff6600;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #e65c00;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 style="color:black;">Login</h2>
    
    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <input type="text" name="username" placeholder="Username or Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="Register.php">Register here</a></p>
</div>

</body>
</html>