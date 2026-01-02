<?php
require_once '../../src/includes/session.php';
require_once '../partials/header.php';
?>

<h2>Register</h2>

<form action="../../src/actions/register_action.php" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="role">Role</label>
        <select name="role" id="role">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
        </select>
    </div>
    <div>
        <button type="submit">Register</button>
    </div>
</form>

<?php require_once '../partials/footer.php'; ?>
