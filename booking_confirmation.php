<?php
require 'config.php';
require 'vendor/autoload.php'; // For composer packages
session_start();

// Verify booking exists
$booking_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;

if (!$booking_id) {
    header("Location: login.php");
    exit();
}

try {
    // Get booking details
    $stmt = $conn->prepare("
        SELECT b.*, r.route_name, r.departure_time, r.departure_coords, r.arrival_coords 
        FROM bookings b
        JOIN routes r ON b.route_id = r.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Generate QR Code
    $qrContent = "Booking ID: $booking_id\nPassenger: {$booking['passenger_name']}\nRoute: {$booking['route_name']}";
    $qrCode = (new \Endroid\QrCode\QrCode($qrContent))
        ->setSize(300)
        ->setMargin(10)
        ->writeDataUri();

    // Generate PDF Ticket
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->Image($qrCode, 15, 25, 50, 50, 'PNG');
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 10, 'Travel Ticket', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, "Passenger: {$booking['passenger_name']}", 0, 1);
    $pdf->Cell(0, 10, "Route: {$booking['route_name']}", 0, 1);
    $pdf->Cell(0, 10, "Departure: ".date('D, M j Y H:i', strtotime($booking['departure_time'])), 0, 1);
    $pdf->Output('ticket.pdf', 'S'); // Store PDF in variable

    // Send confirmation email
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = EMAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = EMAIL_USER;
    $mail->Password = EMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    
    $mail->setFrom(EMAIL_FROM, 'Travel Agency');
    $mail->addAddress($booking['passenger_email']);
    $mail->Subject = 'Your Travel Booking Confirmation';
    $mail->Body = "Your booking (#$booking_id) is confirmed!\n\nRoute: {$booking['route_name']}\nDeparture: {$booking['departure_time']}";
    $mail->addStringAttachment($pdf->Output('ticket.pdf', 'S'), "ticket_$booking_id.pdf");
    $mail->send();

} catch (Exception $e) {
    error_log("Confirmation error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css">
    <style>
        #routeMap { height: 300px; margin: 2rem 0; }
        .qr-code { max-width: 150px; margin: 1rem auto; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="confirmation-card">
            <!-- Existing confirmation content -->

            <!-- QR Code -->
            <div class="text-center">
                <img src="<?= $qrCode ?>" alt="QR Code" class="qr-code">
                <p>Scan QR code for ticket verification</p>
            </div>

            <!-- Route Map -->
            <div id="routeMap"></div>

            <!-- PDF Download -->
            <div class="text-center mt-4">
                <a href="generate_pdf.php?booking_id=<?= $booking_id ?>" class="btn btn-dark">
                    <i class="fas fa-download"></i> Download PDF Ticket
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('routeMap').setView([<?= $booking['departure_coords'] ?>], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Add route markers
        L.marker([<?= $booking['departure_coords'] ?>]).addTo(map)
            .bindPopup('Departure Point');
            
        L.marker([<?= $booking['arrival_coords'] ?>]).addTo(map)
            .bindPopup('Arrival Point');
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>