<?php

// Get the USSD data from the POST request
$phoneNumber = $_POST['phoneNumber'];
$ussdCode = $_POST['ussdCode'];
$text = $_POST['text'];

$response = "";

if ($text == "") {
    // First request, show menu
    $response = "1. Book a trip\n2. Check booking\n3. Pay";
} elseif ($text == "1") {
    // Book a trip
    $response = "Enter destination:";
} elseif (strpos($text, "Enter destination:") !== false) {
    // Process destination input
    $destination = str_replace("Enter destination:", "", $text);
    $response = "Enter number of seats:";
} elseif (strpos($text, "Enter number of seats:") !== false) {
    // Process number of seats input
    $seats = str_replace("Enter number of seats:", "", $text);
    // Add booking logic here
    $response = "Booking confirmed. Pay now? 1.Yes 2.No";
} elseif ($text == "3") {
    // Payment logic
    // Integrate with payment gateway here
    $response = "Payment successful. Booking complete.";
} else {
    $response = "Invalid choice.";
}

// Send the USSD response as JSON
header('Content-Type: application/json');
echo json_encode(['text' => $response]);

?>