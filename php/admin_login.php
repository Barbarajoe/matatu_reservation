<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            echo "Admin logged in successfully!";
            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "Admin not found!";
    }
    $stmt->close();
}
$conn->close();
?>

<!-- Simple HTML Form -->
<form method="POST" action="">
    <label>Username:</label><input type="text" name="username" required><br>
    <label>Password:</label><input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>