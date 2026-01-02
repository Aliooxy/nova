<?php
// tests/seed_products.php

// This script populates the database with sample categories and products.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "Starting database seeding...\n";

try {
    // --- Sample Data ---
    $categories = [
        'Electronics',
        'Books',
        'Services'
    ];

    $products = [
        ['name' => 'Smartphone X', 'description' => 'A high-end smartphone with a great camera.', 'price' => 799.99, 'category' => 'Electronics', 'stock' => 50, 'product_type' => 'physical'],
        ['name' => 'Laptop Pro', 'description' => 'A powerful laptop for professionals.', 'price' => 1299.99, 'category' => 'Electronics', 'stock' => 30, 'product_type' => 'physical'],
        ['name' => 'The Art of PHP', 'description' => 'A book about modern PHP development.', 'price' => 29.99, 'category' => 'Books', 'stock' => 100, 'product_type' => 'physical'],
        ['name' => 'Ebook: Learning SQL', 'description' => 'A digital book on SQL.', 'price' => 19.99, 'category' => 'Books', 'stock' => 9999, 'product_type' => 'download'],
        ['name' => 'Web Design Service', 'description' => 'A one-hour consultation service.', 'price' => 150.00, 'category' => 'Services', 'stock' => 100, 'product_type' => 'service'],
        ['name' => 'Wireless Headphones', 'description' => 'Noise-cancelling over-ear headphones.', 'price' => 199.99, 'category' => 'Electronics', 'stock' => 75, 'product_type' => 'physical'],
    ];

    // --- Clean Existing Data ---
    echo "Clearing existing products and categories...\n";
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("TRUNCATE TABLE order_items");
    $conn->query("TRUNCATE TABLE products");
    $conn->query("TRUNCATE TABLE categories");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo " -> Done.\n";

    // --- Insert Categories ---
    echo "Inserting categories...\n";
    $category_ids = [];
    $sql_cat = "INSERT INTO categories (name) VALUES (?)";
    $stmt_cat = $conn->prepare($sql_cat);

    if (!$stmt_cat) {
        throw new Exception("Prepare failed (categories): (" . $conn->errno . ") " . $conn->error);
    }

    foreach ($categories as $category_name) {
        $stmt_cat->bind_param("s", $category_name);
        $stmt_cat->execute();
        $category_ids[$category_name] = $stmt_cat->insert_id;
        echo " -> Inserted category: $category_name\n";
    }
    $stmt_cat->close();
    echo " -> Category insertion complete.\n";

    // --- Insert Products ---
    echo "Inserting products...\n";
    $sql_prod = "INSERT INTO products (name, description, price, category_id, stock, product_type) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_prod = $conn->prepare($sql_prod);

    if (!$stmt_prod) {
        throw new Exception("Prepare failed (products): (" . $conn->errno . ") " . $conn->error);
    }

    foreach ($products as $product) {
        $category_id = $category_ids[$product['category']] ?? null;
        $stmt_prod->bind_param(
            "ssdiis",
            $product['name'],
            $product['description'],
            $product['price'],
            $category_id,
            $product['stock'],
            $product['product_type']
        );
        $stmt_prod->execute();
        echo " -> Inserted product: " . $product['name'] . "\n";
    }
    $stmt_prod->close();
    echo " -> Product insertion complete.\n";

    echo "------------------------------------\n";
    echo "Database seeding completed successfully!\n";
    echo "------------------------------------\n";

} catch (Exception $e) {
    echo "------------------------------------\n";
    echo "An error occurred during seeding: " . $e->getMessage() . "\n";
    echo "------------------------------------\n";
    exit(1); // Indicate failure
}

$conn->close();
?>