<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Ensure this initializes $conn as a PDO instance

$message = ""; // Variable to store feedback messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['role'])) {
        
        // Sanitize inputs
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']); // Get role from the form (lowercase)
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $confirm_password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password 
        
        // Ensure role is valid (in lowercase)
        $valid_roles = ['passenger', 'operator', 'admin'];
        if (!in_array($role, $valid_roles)) {
            $message = "Invalid role selected.";
        } else {
            try {
                // Insert into the database using uppercase "Role" for the column name
                $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, Role) VALUES (:username, :email, :password, :role)");
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password);
                $stmt->bindParam(':role', $role); // Bind the role to the uppercase "Role" column

                if ($stmt->execute()) {
                    // Redirect to login page
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $message = "Error executing query: " . $errorInfo[2];
                }
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            background: #d3d3d3; /* Light Grey */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h2 {
            color: #ff6600; /* Orange */
        }
        input, select, button {
            width: 90%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #ff6600; /* Orange */
            color: white;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #e65c00; /* Darker Orange */
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 style="color:black;">Register</h2>
    
    <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="passenger">Passenger</option>
            <option value="operator">Operator</option>
            <option value="admin">Administrator</option>
        </select>
        <button type="submit">Register</button>
    </form>
<p>You have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
