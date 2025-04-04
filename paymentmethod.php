<?php
session_start();
// Check if user is logged in and has a valid booking ID
// If not, redirect to bookticket.html
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if (!isset($_GET['booking_id']) || !isset($_SESSION['user_id'])) {
    header("Location: bookticket.html");
    exit();
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
// Replace this query in paymentmethod.php
$booking = $conn->query("
    SELECT b.* 
    FROM bookings b
    WHERE b.booking_id = $booking_id AND b.user_id = {$_SESSION['user_id']}
")->fetch();

if (!$booking) {
    die("Invalid booking");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Method</title>
    <script src="https://js.stripe.com/v3/"></script> <!-- For card payments -->
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
          overflow-x: hidden; /* Prevent horizontal scrollbar */
        }
        
        /* Navbar Styles */
        .navbar {
          background-color: #007bff;
          padding: 10px 20px;
          color: white;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap; /* Make navbar responsive */
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .navbar .logo img {
          height: 40px; /* Adjust as needed */
          margin-right: 10px;
        }
        
        .navbar .logo span{
            color: white;
            margin-left: 10px;
        }
        
        .navbar ul {
          list-style: none;
          display: flex;
          margin: 0;
          padding: 0;
          flex-wrap: wrap; /* Allows items to wrap on small screens */
          gap: 15px; /* Add some gap between navbar items */
        }
        
        .navbar ul li {
          margin: 0;
        }
        
        .navbar ul li a {
          color: white;
          text-decoration: none;
          padding: 8px 15px;
          border-radius: 5px;
          transition: background-color 0.3s ease;
        }
        
        .navbar ul li a:hover {
          background-color: #0056b3;
        }
        
        /* Responsive Navbar */
        @media (max-width: 768px) {
          .navbar {
            flex-direction: column;
            align-items: flex-start;
          }
          .navbar ul {
            flex-direction: column;
            width: 100%;
          }
          .navbar ul li {
            width: 100%;
            text-align: center;
          }
        }
        
        
        /* Main Content Styles */
        .container {
          max-width: 800px; /* Increased max-width for report content */
          margin: 20px auto;
          padding: 20px;
          background-color: white;
          border-radius: 8px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .container h2{
            text-align: center;
            margin-bottom: 20px;
        }
        
        
        /* Dashboard Grid Styles */
        .payment-options {
          display: flex;
          justify-content: center;
          margin-bottom: 20px;
        }
        .payment-options button{
            padding: 10px 20px;
            margin: 0 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .payment-options button:hover{
            background-color: #e9ecef;
        }
        .payment-options button.active{
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        
        /* Stats Card Styles */
        .payment-method {
          display: none;
          padding: 20px;
          background-color: #e9ecef;
          border-radius: 8px;
          text-align: center;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .payment-method.active{
            display: block;
        }
        
        
        .payment-method h3 {
          margin-bottom: 10px;
          color: #343a40;
        }
        
        .payment-method p {
          font-size: 1.5em; /* Increased font size for stats */
          color: #007bff;
          font-weight: bold;
        }
        .form-group {
          margin-bottom: 15px;
          text-align: left;
        }
        
        .form-group label {
          display: block;
          margin-bottom: 5px;
          color: #555;
          font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group input[type="tel"],
        .form-group input[type="month"] {
          width: 100%;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 5px;
          box-sizing: border-box;
          transition: border-color 0.3s ease;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="month"]:focus {
          border-color: #007bff;
          outline: none;
        }
        .form-grid{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        
        /* Footer Styles */
        .footer {
          background-color: #333;
          color: white;
          padding: 20px;
          text-align: center;
          margin-top: auto; /* Push footer to bottom */
        }
        
        .footer-container {
          display: flex;
          flex-direction: column;
          align-items: center;
        }
        
        .footer-links ul {
          list-style: none;
          display: flex;
          margin: 0 0 10px 0;
          padding: 0;
          gap: 15px;
          flex-wrap: wrap; /* Add this */
          justify-content: center; /* center the footer links */
        }
        
        .footer-links ul li a {
          color: white;
          text-decoration: none;
          transition: color 0.3s ease;
        }
        
        .footer-links ul li a:hover {
          color: #007bff;
        }
        
        .footer-copy p {
          font-size: 0.9em;
          margin: 0;
        }
        
        /* Make footer responsive */
        @media (max-width: 768px) {
          .footer-links ul {
            flex-direction: column;
            align-items: center;
          }
          .footer-links ul li{
            margin: 5px 0;
          }
        .form-grid {
            grid-template-columns: 1fr;
        }
        }
            </style>
    
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h2>Select Payment Method</h2>
        
        <!-- Payment Method Selection -->
        <div class="payment-options">
            <button class="payment-tab active" data-method="mpesa">M-Pesa</button>
            <button class="payment-tab" data-method="card">Credit/Debit Card</button>
        </div>

        <!-- Payment Forms -->
        <form id="payment-form" action="process_payment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          

            <!-- M-Pesa Payment Section -->
            <form id="mpesa-payment-form" action="mpesa_payment.php" method="POST">
              <div class="form-group">
                  <label>M-Pesa Phone Number</label>
                  <input type="tel" name="mpesa_phone" 
                         pattern="2547[0-9]{8}"
                         title="Enter phone in 2547XXXXXXXX format"6
                         required>
              </div>
              <div class="form-group">
                  <button type="submit" class="btn btn-primary">Pay with M-Pesa</button>
              </div>
          </form>
                <div class="form-group">
                    <p class="instruction">You will receive a payment prompt on your phone</p>
                </div>
            </div>

            <!-- Card Payment Section -->
            <div class="payment-method" id="card-form">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" 
                           pattern="\d{16}"
                           title="16-digit card number"
                           placeholder="4242 4242 4242 4242">
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="month" name="expiry_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" 
                               pattern="\d{3}"
                               title="3-digit CVV"
                               required>
                    </div>
                </div>
            </div>

            <!-- Common Elements -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Complete Payment</button>
            </div>
        </form>
    </div>

    <script>
        // Toggle payment methods
        document.querySelectorAll('.payment-tab').forEach(button => {
            button.addEventListener('click', (e) => {
                // Remove active classes
                document.querySelectorAll('.payment-tab, .payment-method').forEach(el => {
                    el.classList.remove('active');
                });

                // Add active class to selected method
                const method = e.target.dataset.method;
                e.target.classList.add('active');
                document.getElementById(`${method}-form`).classList.add('active');
                
                // Update form validation
                document.querySelectorAll('input').forEach(input => {
                    input.required = input.closest('.payment-method.active') ? true : false;
                });
            });
        });

        // Handle card payments with Stripe
        const stripe = Stripe('your_publishable_key');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const paymentMethod = document.querySelector('.payment-tab.active').dataset.method;
            
            if(paymentMethod === 'card') {
                const {error, paymentMethod} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement
                });

                if (error) {
                    alert(error.message);
                } else {
                    // Add payment method ID to form
                    const hiddenInput = document.createElement('input');
                    hiddenInput.setAttribute('type', 'hidden');
                    hiddenInput.setAttribute('name', 'payment_method_id');
                    hiddenInput.setAttribute('value', paymentMethod.id);
                    form.appendChild(hiddenInput);
                    
                    // Submit form
                    form.submit();
                }
            } else {
                // Handle M-Pesa payment
                form.submit();
            }
        });
         // Check for success query parameter in the URL
    if (urlParams.has('success')) {
        alert('Payment successful! Thank you for your booking.');
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>