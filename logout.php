<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID first to prevent session fixation
session_regenerate_id(true);

// Unset all session variables
$_SESSION = array();

<<<<<<< HEAD
// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
=======
// Ensure headers are not already sent before redirecting
if (!headers_sent()) {
    header("Location: login.php");
    exit();
} else {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
>>>>>>> 402a3c4c927ce72e0d810dd8f77d4e374af7042c
}

// Destroy the session
session_destroy();

// Clear any remaining session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Additional security headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page
header("Location: login.html");
exit();
?>