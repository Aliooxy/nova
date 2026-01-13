<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/includes/session.php';

// Security check: only admins can access this page
check_login();
check_role('admin');

// 1. Get and validate user ID from URL
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: /views/admin/users.php");
    exit;
}

// 2. Fetch user data from the database
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
if (!$stmt) {
    $_SESSION['error'] = "Database error.";
    header("Location: /views/admin/users.php");
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: /views/admin/users.php");
    exit;
}

require_once __DIR__ . '/../partials/header.php';
?>

<h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>

<?php
if (isset($_SESSION['error'])) {
    echo '<p class="message error">' . htmlspecialchars($_SESSION['error']) . '</p>';
    unset($_SESSION['error']);
}
?>

<form action="/src/actions/update_user_action.php" method="post" class="styled-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>

    <div class="form-group">
        <label for="password">New Password (optional)</label>
        <input type="password" id="password" name="password">
        <small>Leave blank to keep the current password.</small>
    </div>

    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="buyer" <?php echo ($user['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
            <option value="seller" <?php echo ($user['role'] === 'seller') ? 'selected' : ''; ?>>Seller</option>
            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="/views/admin/users.php" class="btn">Cancel</a>
    </div>
</form>

<?php
$conn->close();
require_once __DIR__ . '/../partials/footer.php';
?>
