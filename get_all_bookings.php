<?php
include 'config.php';

$sql = "SELECT users.username, routes.route_name, bookings.status, bookings.booking_date
FROM bookings
JOIN users ON bookings.user_id = users.user_id
JOIN routes ON bookings.route_id = routes.route_id";

$result = $conn->query($sql);

while($row = $result->fetch(PDO::FETCH_ASSOC)) {
echo "<tr>
    <td>{$row['username']}</td>
    <td>{$row['route_name']}</td>
    <td>{$row['status']}</td>
    <td>{$row['booking_date']}</td>
  </tr>";
}
$conn = null;
?>