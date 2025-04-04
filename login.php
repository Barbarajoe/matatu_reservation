<!-- filepath: c:\xampp\htdocs\matatu_reservation\login.php -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config.php"; // Include the database configuration file
include('navbar.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user input
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    try {
        // Prepare and execute the query using PDO
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: booktickets.html");
            exit();
        } else {
            // Handle incorrect credentials
            $_SESSION['error_message'] = $user ? "Incorrect password!" : "User not found!";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<style>
    /* Base Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .container {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        width: 350px;
        text-align: center;
    }

    h2 {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        text-align: center;
    }

    .form-group {
        text-align: left;
        margin-bottom: 1rem;
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 5px;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
    }

    .btn {
        background: #303f9f;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        width: 100%;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn:hover {
        background: #1a237e;
    }

    .toggle-link {
        display: block;
        margin-top: 1rem;
        color: #303f9f;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
</style>
<body>
<div class="container">
    <h2>Login</h2>

    <?php
    if (isset($_SESSION['error_message'])) {
        echo "<p style='color:red;'>" . $_SESSION['error_message'] . "</p>";
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter Username" required>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <a href="Register.php" class="toggle-link">Don't have an account? Sign up here</a>
</div>

<script src="SignIn.js"></script>

</body>
</html>