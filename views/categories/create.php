<?php
require_once __DIR__ . '/../../src/includes/session.php';

// Security check: only admins and sellers can access this page
check_login();
if (!in_array($_SESSION['role'], ['admin', 'seller'])) {
    $_SESSION['error'] = "You are not authorized to access this page.";
    header("Location: /index.php");
    exit;
}

require_once __DIR__ . '/../partials/header.php';
?>

<h2>Add New Category</h2>

<?php
if (isset($_SESSION['error'])) {
    echo '<p class="message error">' . htmlspecialchars($_SESSION['error']) . '</p>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p class="message success">' . htmlspecialchars($_SESSION['success']) . '</p>';
    unset($_SESSION['success']);
}
?>

<form action="/src/actions/create_category_action.php" method="post" class="styled-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">

    <div class="form-group">
        <label for="name">Category Name</label>
        <input type="text" id="name" name="name" required>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Add Category</button>
    </div>
</form>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>
