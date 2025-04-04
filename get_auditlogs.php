<?php
require 'config.php';

try {
    $stmt = $connect->query("
        SELECT audit_logs.*, users.username 
        FROM audit_logs 
        JOIN users ON audit_logs.user_id = users.user_id 
        ORDER BY timestamp DESC
    ");
    
    while($log = $stmt->fetch_assoc()) {
        echo "<tr>
                <td>{$log['timestamp']}</td>
                <td>{$log['username']}</td>
                <td>{$log['action_details']}</td>
                <td>{$log['ip_address']}</td>
              </tr>";
    }
} catch(PDOException $e) {
    echo "<tr><td colspan='4'>Error loading logs</td></tr>";
}
?>