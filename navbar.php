<?php if(isset($_SESSION['user_id'])): ?>
<nav class="navbar">
    <div class="logo">
        <img src="Logo.jpg" alt="Matatu System">
        <span><?= htmlspecialchars($_SESSION['username']) ?> (<?= ucfirst($_SESSION['role']) ?>)</span>
    </div>
    <ul>
        <?php if($_SESSION['role'] === 'administrator'): ?>
            <li><a href="administrator_home.html">Dashboard</a></li>
            <li><a href="user_management.html">Users</a></li>
            <li><a href="system_reports.html">SystemReports</a></li>
            <li><a href="audit_logs.html">AuditLogs</a></li>
        <?php elseif($_SESSION['role'] === 'operator'): ?>
            <li><a href="operator_home.html">Dashboard</a></li>
            <li><a href="manage_routes.html">Routes</a></li>
            <li><a href="allbookings.html">AllBookings</a></li>
            <li><a href="financial_reports.html">FinancialReports</a></li>
        <?php else: ?>
            <li><a href="passenger_home.html">Home</a></li>
            <li><a href="booktickets.html">Book Ticket</a></li>
            <li><a href="View_Bookings.html">ViewBookings</a></li>
        <?php endif; ?>
        <li><a href="profile.html">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>
<?php endif; ?>

<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Delete session cookie
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
}

// Destroy session
session_destroy();

// Prevent caching of the page
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirect to login
header("Location: login.html");
exit();
?>