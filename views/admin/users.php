<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/includes/session.php';

// Security check: only admins can access this page
check_login();
check_role('admin');

// Fetch all users from the database
$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>

<h2>User Management</h2>

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

<div class="table-container">
    <table class="styled-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                        <td><?php echo date("Y-m-d", strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="/views/admin/edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm">Edit</a>

                            <!-- Prevent admin from deleting themselves -->
                            <?php if ($_SESSION['id'] !== $user['id']): ?>
                                <form action="/src/actions/delete_user_action.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once __DIR__ . '/../partials/footer.php';
?>
