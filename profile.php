<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.navbar {
    background-color: #333;
    color: white;
    padding: 20px;
    width: 150px; /* Sidebar width */
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    z-index: 1;
    height: 100vh; /* Ensures the sidebar extends fully */
}

.navbar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.navbar ul li {
    margin-bottom: 20px;
}

.navbar ul li a {
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
    display: block;
}

.navbar ul li a:hover {
    background-color: #ff6600;
}

.navbar .logo {
    text-align: center;
    margin-bottom: 20px;
}

.navbar .logo img {
    height: 100px;
    margin-bottom: 10px;
}

.navbar .logo span {
    color: white;
    font-size: 20px;
}

.main-content {
    margin-left: 200px; /* Space for the sidebar */
    padding: 20px;
    width: calc(100% - 200px); /* Make space for sidebar */
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow-x: hidden;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.container h2 {
    text-align: center;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

label {
    font-size: 14px;
    font-weight: bold;
    color: #555;
    display: block;
    margin-bottom: 8px;
}

input[type="text"], input[type="date"], input[type="number"], select {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus, input[type="date"]:focus, input[type="number"]:focus, select:focus {
    border-color: #ff6600;
    background-color: #fff;
    outline: none;
}

button, .btn {
    padding: 10px 20px;
    font-size: 14px;
    color: white;
    background-color: #ff6600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease;
}

button:hover, .btn:hover {
    background-color: #e65c00;
}

.text-center {
    text-align: center;
}

.footer {
    background-color: #333;
    color: white;
    padding: 20px;
    text-align: center;
    margin-top: auto; /* Pushes footer to the bottom */
    width: 100%;
}

.footer-container {
    max-width: 100%; /* Makes sure the content inside the footer container spans the full width */
    margin: 0;
    padding: 0;
}

.footer-links ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.footer-links ul li a {
    color: white;
    text-decoration: none;
}

.footer-links ul li a:hover {
    color: #ff6600;
}

.footer-copy p {
    margin-top: 10px;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .navbar {
        width: 100%;
        height: auto;
        flex-direction: row;
        justify-content: space-between;
        padding: 10px;
    }

    .navbar ul {
        display: flex;
        flex-direction: row;
    }

    .navbar ul li {
        margin: 0 20px;
    }

    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .container {
        width: 90%;
        margin: 20px auto; /* Adjust width for smaller devices */
    }

    .footer-links ul {
        flex-direction: column;
        align-items: center;
    }

    .footer-links ul li {
        margin-bottom: 10px;
    }
}

@media (max-width: 500px) {
    .navbar ul li {
        margin: 0 10px;
    }

    .container {
        padding: 15px;
    }

    button, .btn {
        width: 100%;
        padding: 15px;
    }
}

    </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">
        <a class="navbar-brand" href="passenger_home.html">
        <img src="Logo.jpg" width="120" alt="Matatu System" href="passenger_home.html">
        </div>
    <div class="navbar-links">
    <ul>
        <li><a href="passenger_home.html">Home</a></li>
        <li><a href="booktickets.html">Book Ticket</a></li>
        <li><a href="View_Booking.html">My Bookings</a></li>
        <li><a href="Profile.html">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
    </div>
</nav>

    <div class="container">
        <h2>Profile Settings</h2>
        <form id="profile-form" class="view-mode">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" id="username" title="Username" placeholder="Enter your username" readonly>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" id="email" title="Email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="tel" name="phone" id="phone" title="Phone" placeholder="Enter your phone number" required>
            </div>
            <div class="form-group" id="password-group">
                <label>New Password:</label>
                <input type="password" name="password" id="password" placeholder="••••••••">
            </div>
            <div class="button-group">
                <button type="button" id="edit-button">Edit</button>
                <button type="button" id="cancel-button">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript code (as you had it previously)
        const editBtn = document.getElementById('edit-button');
        const cancelBtn = document.getElementById('cancel-button');
        const passwordGroup = document.getElementById('password-group');
        const emailInput = document.getElementById("email");
        const phoneInput = document.getElementById("phone");

        // Toggle edit mode
        function toggleEditMode(edit) {
            if (edit) {
                emailInput.removeAttribute('readonly');
                phoneInput.removeAttribute('readonly');
                passwordGroup.style.display = 'block';
                editBtn.style.display = 'none';
                cancelBtn.style.display = 'inline-block';
            } else {
                emailInput.setAttribute('readonly', true);
                phoneInput.setAttribute('readonly', true);
                passwordGroup.style.display = 'none';
                editBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'none';
            }
        }

        editBtn.addEventListener('click', () => toggleEditMode(true));
        cancelBtn.addEventListener('click', () => toggleEditMode(false));
    </script>
</body>
</html>
