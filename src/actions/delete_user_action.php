<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// --- Security Checks ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /index.php");
    exit;
}

check_login();
check_role('admin');

if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "CSRF token validation failed.";
    header("Location: /views/admin/users.php");
    exit();
}

// --- Data Sanitization and Validation ---
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if (!$user_id) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: /views/admin/users.php");
    exit;
}

// Prevent an admin from deleting their own account
if ($user_id === $_SESSION['id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: /views/admin/users.php");
    exit;
}

// --- Database Deletion ---
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("SQL Prepare Error: " . $conn->error);
    $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
    header("Location: /views/admin/users.php");
    exit;
}

$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Check if any row was actually deleted
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "User has been deleted successfully.";
    } else {
        $_SESSION['error'] = "User not found or could not be deleted.";
    }
} else {
    error_log("SQL Execute Error: " . $stmt->error);
    $_SESSION['error'] = "Failed to delete the user. They may have associated orders or other records.";
}

$stmt->close();
$conn->close();
header("Location: /views/admin/users.php");
exit;
?>
