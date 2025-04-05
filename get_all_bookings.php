<?php
include 'config.php';

$sql = "SELECT 
            users.username, 
            routes.departure_point, 
            routes.arrival_point, 
            bookings.status, 
            bookings.booking_date
        FROM bookings
        JOIN users ON bookings.user_id = users.user_id
        JOIN routes ON bookings.route_id = routes.route_id";

$result = $connect->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['username']}</td>
        <td>{$row['departure_point']}</td>
        <td>{$row['arrival_point']}</td>
        <td>{$row['status']}</td>
        <td>{$row['booking_date']}</td>
    </tr>";
}

$conn = null;
?>