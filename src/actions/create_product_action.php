<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// --- Security Checks ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Only allow POST requests
    header("Location: /index.php");
    exit;
}

check_login();

// Only admins and sellers can create products
if (!in_array($_SESSION['role'], ['admin', 'seller'])) {
    $_SESSION['error'] = "You are not authorized to perform this action.";
    header("Location: /index.php");
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "CSRF token validation failed.";
    header("Location: /views/products/create.php");
    exit();
}

// --- Data Sanitization and Validation ---
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
$product_type = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_STRING);

$errors = [];

if (empty($name)) {
    $errors[] = "Product name is required.";
}
if ($price === false || $price < 0) {
    $errors[] = "A valid, non-negative price is required.";
}
if ($category_id === false) {
    $errors[] = "A valid category must be selected.";
}
if ($stock === false || $stock < 0) {
    $errors[] = "A valid, non-negative stock quantity is required.";
}
if (!in_array($product_type, ['physical', 'download', 'service'])) {
    $errors[] = "Invalid product type selected.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    header("Location: /views/products/create.php");
    exit;
}


// --- Database Insertion ---
$sql = "INSERT INTO products (name, description, price, category_id, stock, product_type) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // If prepare fails, it's a server error
    error_log("SQL Prepare Error: " . $conn->error);
    $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
    header("Location: /views/products/create.php");
    exit;
}

$stmt->bind_param(
    "ssdiis",
    $name,
    $description,
    $price,
    $category_id,
    $stock,
    $product_type
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Product '" . htmlspecialchars($name) . "' has been added successfully!";
    // Redirect to the appropriate dashboard
    $dashboard = ($_SESSION['role'] === 'admin') ? '/admin_dashboard.php' : '/seller_dashboard.php';
    header("Location: " . $dashboard);
} else {
    error_log("SQL Execute Error: " . $stmt->error);
    $_SESSION['error'] = "Failed to add the product. Please try again.";
    header("Location: /views/products/create.php");
}

$stmt->close();
$conn->close();
exit;
?>
