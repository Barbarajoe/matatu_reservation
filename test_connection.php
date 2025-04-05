<?php
require 'config.php'; // Include the database connection file

// Test query
$query = "SELECT 1 AS test_value";
$result = mysqli_query($connect, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Connection successful! Test value: " . $row['test_value'];
} else {
    echo "Query failed: " . mysqli_error($connect);
}

// Close connection
mysqli_close($connect);
?>