<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Check if username or email already exists
    $check = $conn->prepare("SELECT id FROM passengers WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Username or email already exists!";
    } else {
        // Insert new passenger
        $stmt = $conn->prepare("INSERT INTO passengers (username, password, phone, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $phone, $email);

        if ($stmt->execute()) {
            echo "Passenger registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
}
$conn->close();
?>

<!-- Simple HTML Form -->
<form method="POST" action="">
    <label>Username:</label><input type="text" name="username" required><br>
    <label>Password:</label><input type="password" name="password" required><br>
    <label>Phone:</label><input type="text" name="phone" required><br>
    <label>Email:</label><input type="email" name="email" required><br>
    <input type="submit" value="Register">
</form>