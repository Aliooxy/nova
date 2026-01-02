<?php
// src/includes/session.php starts the session
require_once 'src/includes/session.php';

// Check if the user is logged in and is a seller
check_login();
check_role('seller');

// Include the header
require_once 'views/partials/header.php';
?>

<h2>Seller Dashboard</h2>
<p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
<p>This is the seller dashboard. You can manage your products and orders here.</p>
<p><a href="logout.php">Logout</a></p>

<?php
// Include the footer
require_once 'views/partials/footer.php';
?>
