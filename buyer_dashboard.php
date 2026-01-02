<?php
// src/includes/session.php starts the session
require_once 'src/includes/session.php';

// Check if the user is logged in and is a buyer
check_login();
check_role('buyer');

// Include the header
require_once 'views/partials/header.php';
?>

<h2>Buyer Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
<p>This is your dashboard. You can view your orders and manage your profile here.</p>
<p><a href="logout.php">Logout</a></p>

<?php
// Include the footer
require_once 'views/partials/footer.php';
?>
