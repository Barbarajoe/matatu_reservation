<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign Up</title>
  <link rel="stylesheet" type="text/css" href="Styles.css">
  <style>

  body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
  }

  .signup-form {
    width: 50%;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
  }

  h1 {
    text-align: center;
    color: #333;
  }

  label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
  }

  input, select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .password-container {
    position: relative;
  }

  .password-container input {
    width: 100%;
  }

  .toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
  }

  .error-message {
    color: red;
    font-size: 14px;
  }

  button {
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
  }

  button:hover {
    background-color: #218838;
  }

  p {
    text-align: center;
    margin-top: 10px;
  }

  a {
    color: #007bff;
    text-decoration: none;
  }

  a:hover {
    text-decoration: underline;
  }
</style>

  </style>
</head>
<body>
  <header>
    <h1>Sign Up</h1>
  </header>
  
  <div class="form signup-form">
    <form id="signupForm" action="Signup.php" method="POST">

      <label for="username">Username:</label>
      <input type="text" id="username" name="username" placeholder="Enter Username" oninput="validateUsername()">
      <span class="error-message" id="username-error"></span>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Enter Email" oninput="validateEmail()">
      <span class="error-message" id="email-error"></span>
      <label for="email">Phone number:</label>
      <input type="tel" id="phone " name="phone" placeholder="Enter Phone number" oninput="validatePhonenumber()">
      <span class="error-message" id="Phone-error"></span>

      <label for="password">Password:</label>
      <div class="password-container">
        <input type="password" id="password" name="password" placeholder="Enter password" oninput="validatePassword()">
        <span class="toggle-password"></span>
      </div>
      <span class="error-message" id="password-error"></span>

      <label for="confirm_password">Confirm Password:</label>
      <div class="password-container">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password" oninput="validateConfirmPassword()">
        <span class="toggle-password"></span>
      </div>
      <span class="error-message" id="confirm-password-error"></span>

      <button type="submit" onclick="return validateForm()">Sign Up</button><br>
      <p style="text-align: center;">Already have an account? <a href="Login.php">Log in here</a></p>

    </form>
    <?php
// Include database connection

include "api/config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

    // Pick the data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone_number = trim($_POST['phone']);

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Write SQL query to insert data (Prepared Statement)
    $query = "INSERT INTO users (username, email, phone, password_hash) VALUES (?, ?, ?, ?)";
    
    // Prepare the statement
    $stmt = mysqli_prepare($connect, $query);
    
    if ($stmt) {
        // Bind parameters (s = string)
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $phone_number, $hashed_password);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to login page
            header("Location: Login.php?Signup=success");
            exit();
        } else {
            die("Database Error: " . mysqli_stmt_error($stmt));
        }

        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        die("Failed to prepare the statement: " . mysqli_error($connect));
    }
}

// Close database connection
mysqli_close($connect);
?>


  </div>
 
  <script src="SignIn.js"></script>
</body>
</html>