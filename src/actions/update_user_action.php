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
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
$password = $_POST['password']; // Not sanitized as it's for hashing

$errors = [];

if (!$user_id) {
    $errors[] = "Invalid user ID.";
}
if (empty($username)) {
    $errors[] = "Username is required.";
}
if ($email === false) {
    $errors[] = "A valid email is required.";
}
if (!in_array($role, ['admin', 'seller', 'buyer'])) {
    $errors[] = "Invalid role selected.";
}

// Prevent admin from changing their own role to non-admin if they are the last admin
if ($_SESSION['id'] === $user_id && $role !== 'admin') {
    $stmt_check = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    if ($row['admin_count'] <= 1) {
        $errors[] = "Cannot change your own role as you are the only administrator.";
    }
    $stmt_check->close();
}


if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: /views/admin/edit_user.php?id=" . $user_id);
    exit;
}


// --- Database Update ---
$sql_parts = [];
$params = [];
$types = '';

$sql_parts[] = "username = ?";
$params[] = &$username;
$types .= 's';

$sql_parts[] = "email = ?";
$params[] = &$email;
$types .= 's';

$sql_parts[] = "role = ?";
$params[] = &$role;
$types .= 's';

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql_parts[] = "password = ?";
    $params[] = &$hashed_password;
    $types .= 's';
}

$params[] = &$user_id;
$types .= 'i';

$sql = "UPDATE users SET " . implode(', ', $sql_parts) . " WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("SQL Prepare Error: " . $conn->error);
    $_SESSION['error'] = "An unexpected error occurred.";
    header("Location: /views/admin/edit_user.php?id=" . $user_id);
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['success'] = "User '" . htmlspecialchars($username) . "' has been updated successfully!";
    header("Location: /views/admin/users.php");
} else {
    // Check for duplicate entry on username or email
    if ($stmt->errno == 1062) {
         $_SESSION['error'] = "This username or email is already taken by another user.";
    } else {
        error_log("SQL Execute Error: " . $stmt->error);
        $_SESSION['error'] = "Failed to update the user.";
    }
    header("Location: /views/admin/edit_user.php?id=" . $user_id);
}

$stmt->close();
$conn->close();
exit;
?>
