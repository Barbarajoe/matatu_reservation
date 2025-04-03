<?php
session_start();

// Ensure session exists before unsetting/destroying
if (isset($_SESSION)) {
    session_unset();
    $_SESSION = []; // Clear session data
setcookie(session_name(), '', time() - 3600, '/'); // Delete session cookie

    session_destroy();
}

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Prevent session fixation attacks
session_regenerate_id(true);


// Ensure headers are not already sent before redirecting
if (!headers_sent()) {
    header("Location: login.php");
    exit();
} else {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}
?>
