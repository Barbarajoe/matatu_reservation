<?php
include 'config.php';

$sql = "SELECT users.username, routes.origin, routes.destination, bookings.payment_status, bookings.booking_time
FROM bookings
JOIN users ON bookings.user_id = users.user_id
JOIN routes ON bookings.route_id = routes.route_id";

$result = $conn->query($sql);

while($row = $result->fetch(PDO::FETCH_ASSOC)) {
echo "<tr>
    <td>{$row['username']}</td>
    <td>{$row['origin']} - {$row['destination']}</td>
    <td>{$row['payment_method']}</td>
    <td>{$row['booking_time']}</td>
  </tr>";
}
$conn = null;
?>