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

<div class="dashboard-actions">
    <a href="/views/products/create.php" class="btn">Add New Product</a>
    <a href="/views/categories/create.php" class="btn">Add New Category</a>
    <a href="/views/admin/users.php" class="btn">Manage Users</a>
    <!-- More admin actions can be added here -->
</div>

<p><a href="logout.php">Logout</a></p>

<?php
// Include the footer
require_once 'views/partials/footer.php';
?>
