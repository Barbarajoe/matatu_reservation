<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Transit | Matatu Reservation System</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: black;
        }
        .navbar {
            background-color: black;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }
        .navbar .logo {
            display: flex;
            align-items: center;
        }
        .navbar img {
            height: 50px;
            margin-right: 10px;
        }
        .navbar a {
    color: white;
    text-decoration: none;
    margin: 0 15px;
}

.navbar a:hover {
    color: #FF7300; /* Orange color on hover */
}

        .content {
            text-align: center;
            padding: 50px;
            background: url('background.jpg') no-repeat center center;
            background-size: cover;
        }
        .cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 40px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card img {
            width: 60px;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            font-size: 18px;
            color: white;
            background-color: #FF7300;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #e65c00;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="Logo.jpg" alt="Urban Transit Logo">
            <strong>Urban Transit</strong>
        </div>
        <div>
            <a href="index.php">Home</a>
            <a href="About_us.html">About Us</a>
            <a href="Contact.html">Contact Us</a>
            <a href="Terms_of_service.html">Terms of service</a>
            <a href="Register.php">Sign In/Register</a>
        </div>
    </div>
    
    <div class="content">
        <h1>Welcome to Urban Transit</h1>
        <p>Book your matatu rides quickly and conveniently.</p>
        <a href="login.php" class="btn">Make a Reservation</a>
    </div>
    
    <h2 style="text-align: center;">Why Us</h2>
    <div class="cards">
        <div class="card">
            <h3>Easy Booking</h3>
            <p>Reserve your matatu seat in just a few clicks with our user-friendly platform.</p>
        </div>
        <div class="card">
           
            <h3>Real-Time Tracking</h3>
            <p>Track your matatu in real time to stay updated on arrivals and delays.</p>
        </div>
        <div class="card">
            
            <h3>Secure Payments</h3>
            <p>Make hassle-free and secure payments through multiple payment options.</p>
        </div>
    </div>
    <footer style="text-align: center; padding: 20px; background-color: black; color: white; margin-top: 20px;">
        &copy; 2025 Urban Transit
    </footer>
</body>
</html>
