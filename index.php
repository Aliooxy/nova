<?php
require_once 'config/database.php';
require_once 'views/partials/header.php';

// --- Fetch Categories for Filter ---
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);


// --- Fetch Products with Filtering and Search ---
$search_term = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($search_term)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $like_search_term = "%" . $search_term . "%";
    $params[] = &$like_search_term;
    $params[] = &$like_search_term;
    $types .= 'ss';
}

if (!empty($category_filter)) {
    $sql .= " AND p.category_id = ?";
    $params[] = &$category_filter;
    $types .= 'i';
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $products_result = $stmt->get_result();
    $products = $products_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle error
    $products = [];
    echo "Error preparing statement: " . $conn->error;
}
?>

<div class="page-header">
    <h1>Welcome to our Simple Store</h1>
    <p>Browse our collection of fine products.</p>
</div>

<!-- Search and Filter Form -->
<aside class="filters">
    <form action="index.php" method="GET" class="filter-form">
        <div class="form-group">
            <input type="text" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search_term); ?>">
        </div>
        <div class="form-group">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
    </form>
</aside>

<!-- Product Grid -->
<section class="product-grid">
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="product-card-image">
                    <!-- Placeholder for an image -->
                    <img src="https://via.placeholder.com/300" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-card-content">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <div class="product-card-footer">
                        <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products found. Try adjusting your search or filter.</p>
    <?php endif; ?>
</section>


<?php
$conn->close();
require_once 'views/partials/footer.php';
?>
