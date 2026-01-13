<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// --- Security Checks ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /index.php");
    exit;
}

check_login();

if (!in_array($_SESSION['role'], ['admin', 'seller'])) {
    $_SESSION['error'] = "You are not authorized to perform this action.";
    header("Location: /index.php");
    exit;
}

if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "CSRF token validation failed.";
    header("Location: /views/categories/create.php");
    exit();
}

// --- Data Sanitization and Validation ---
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

if (empty($name)) {
    $_SESSION['error'] = "Category name is required.";
    header("Location: /views/categories/create.php");
    exit;
}

// --- Check for Duplicates ---
$stmt_check = $conn->prepare("SELECT id FROM categories WHERE name = ?");
$stmt_check->bind_param("s", $name);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $_SESSION['error'] = "A category with this name already exists.";
    header("Location: /views/categories/create.php");
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// --- Database Insertion ---
$sql = "INSERT INTO categories (name) VALUES (?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("SQL Prepare Error: " . $conn->error);
    $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
    header("Location: /views/categories/create.php");
    exit;
}

$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    $_SESSION['success'] = "Category '" . htmlspecialchars($name) . "' has been added successfully!";
    $dashboard = ($_SESSION['role'] === 'admin') ? '/admin_dashboard.php' : '/seller_dashboard.php';
    header("Location: " . $dashboard);
} else {
    error_log("SQL Execute Error: " . $stmt->error);
    $_SESSION['error'] = "Failed to add the category. Please try again.";
    header("Location: /views/categories/create.php");
}

$stmt->close();
$conn->close();
exit;
?>
