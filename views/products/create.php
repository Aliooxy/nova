<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/includes/session.php';

// Security check: only admins and sellers can access this page
check_login();
if (!in_array($_SESSION['role'], ['admin', 'seller'])) {
    $_SESSION['error'] = "You are not authorized to access this page.";
    header("Location: /index.php");
    exit;
}

// Fetch categories to populate the dropdown
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../partials/header.php';
?>

<h2>Add New Product</h2>

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

<form action="/src/actions/create_product_action.php" method="post" class="styled-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token()); ?>">

    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" id="name" name="name" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>
    </div>

    <div class="form-group">
        <label for="price">Price</label>
        <input type="number" id="price" name="price" step="0.01" min="0" required>
    </div>

    <div class="form-group">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">-- Select a Category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="stock">Stock Quantity</label>
        <input type="number" id="stock" name="stock" min="0" required>
    </div>

    <div class="form-group">
        <label for="product_type">Product Type</label>
        <select id="product_type" name="product_type" required>
            <option value="physical">Physical</option>
            <option value="download">Download</option>
            <option value="service">Service</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Add Product</button>
    </div>
</form>

<?php
$conn->close();
require_once __DIR__ . '/../partials/footer.php';
?>
