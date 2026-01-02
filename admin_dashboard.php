<?php
// src/includes/session.php starts the session
require_once 'src/includes/session.php';

// Check if the user is logged in and is an admin
check_login();
check_role('admin');

// Include the header
require_once 'views/partials/header.php';
?>

<h2>Admin Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
<p>This is the admin dashboard. You have full access to the system.</p>
<p><a href="logout.php">Logout</a></p>

<?php
// Include the footer
require_once 'views/partials/footer.php';
?>
