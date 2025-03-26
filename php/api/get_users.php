<?php
include 'config.php';
header('Content-Type: application/json');

$sql = "SELECT user_id, username, email, role, created_at FROM users";
$result = $conn->query($sql);

$users = [];
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}

echo json_encode($users);
$conn = null;
?>
