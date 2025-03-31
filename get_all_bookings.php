<?php
include 'config.php';

$sql = "SELECT users.username, routes.name, bookings.status, bookings.booking_time
FROM bookings
JOIN users ON bookings.user_id = users.id
JOIN routes ON bookings.route_id = routes.id";

$result = $conn->query($sql);

while($row = $result->fetch(PDO::FETCH_ASSOC)) {
echo "<tr>
    <td>{$row['username']}</td>
    <td>{$row['name']}</td>
    <td>{$row['status']}</td>
    <td>{$row['booking_time']}</td>
  </tr>";
}
$conn = null;
?>