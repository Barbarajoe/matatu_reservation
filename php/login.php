<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "api/config.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT username, password_hash FROM users WHERE username = '$username'";
    $result = mysqli_query($connect, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($connect));
    }

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: passenger/bookticket.html");
        exit();
    } else {
        $_SESSION['error_message'] = $user ? "Incorrect password!" : "User not found!";
        header("Location: login.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="stylesheet" href="styles.Css">
</head>
<style>
    /* Base Styles */
/* General Page Styling */
/* Center the form */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Form Container */
.container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
}

/* Fix Login Heading */
h2 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    text-align: center;
}

/* Form Fields */
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

/* Button */
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

/* Fix Signup Link */
.toggle-link {
    display: block;
    margin-top: 1rem;
    color: #303f9f;
    font-weight: 600;
}


/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin: 1rem;
        padding: 1.5rem;
    }

    .navbar {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
    }

    .navbar ul {
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem;
    }

    .footer-container {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
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

    <a href="Signup.php" class="toggle-link">Don't have an account? Sign up here</a>
</div>

<script src="SignIn.js"></script>

</body>
</html>