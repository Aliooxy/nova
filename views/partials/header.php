<?php
// It's good practice to start the session on all pages
// in case we need to display flash messages or user info.
// __DIR__ gives the directory of the current file (views/partials)
require_once __DIR__ . '/../../src/includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Store</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="/index.php" class="logo">SimpleStore</a>
            <nav class="main-nav">
                <ul>
                    <li><a href="/index.php">Home</a></li>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                        <?php
                            $dashboard_link = '/index.php'; // Default fallback
                            if ($_SESSION['role'] === 'admin') {
                                $dashboard_link = '/admin_dashboard.php';
                            } elseif ($_SESSION['role'] === 'seller') {
                                $dashboard_link = '/seller_dashboard.php';
                            } elseif ($_SESSION['role'] === 'buyer') {
                                $dashboard_link = '/buyer_dashboard.php';
                            }
                        ?>
                        <li><a href="<?php echo $dashboard_link; ?>">My Dashboard</a></li>
                        <li><a href="/logout.php">Logout</a></li>
                        <li class="nav-welcome"><span>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</span></li>
                    <?php else: ?>
                        <li><a href="/views/auth/login.php">Login</a></li>
                        <li><a href="/views/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
