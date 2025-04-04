<?php
// filepath: c:\xampp\htdocs\matatu_reservation_old[1]\matatu_reservation old\mpesa_payment.php

// Daraja API credentials
$consumerKey = 'wJbshILGzQDXg641duYUgoPztjoFvOpqvSpHPAzRe9WpJTd'; // Replace with your Consumer Key
$consumerSecret = 'nUQ8eBDWP9ErkdA7Wf3nVDinILASNdaAqJWHrGRwNHZmT1NMqGmgxjnsp6WFtEn'; // Replace with your Consumer Secret

// Generate access token
function generateAccessToken($consumerKey, $consumerSecret) {
    $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode("$consumerKey:$consumerSecret");

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response);
    return $result->access_token ?? null;
}

// Initiate M-Pesa STK Push
function initiateSTKPush($accessToken, $phoneNumber, $amount) {
    $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $businessShortCode = '174379'; // Replace with your Paybill or Till Number
    $passkey = 'YOUR_PASSKEY'; // Replace with your Passkey
    $timestamp = date('YmdHis');
    $password = base64_encode($businessShortCode . $passkey . $timestamp);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ]);

    $postData = [
        'BusinessShortCode' => $businessShortCode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phoneNumber,
        'PartyB' => $businessShortCode,
        'PhoneNumber' => $phoneNumber,
        'CallBackURL' => 'https://yourdomain.com/callback.php', // Replace with your callback URL
        'AccountReference' => 'MatatuBooking',
        'TransactionDesc' => 'Payment for Matatu Booking'
    ];

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    return json_decode($response);
}

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phoneNumber = $_POST['mpesa_phone']; // Get phone number from form
    $amount = 100; // Replace with the actual amount to charge

    $accessToken = generateAccessToken($consumerKey, $consumerSecret);
    if ($accessToken) {
        $response = initiateSTKPush($accessToken, $phoneNumber, $amount);
        if (isset($response->ResponseCode) && $response->ResponseCode == '0') {
            echo "<script>alert('Payment prompt sent to your phone. Please complete the payment.');</script>";
        } else {
            echo "<script>alert('Failed to initiate payment. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Failed to generate access token. Please try again.');</script>";
    }
}
?>