<?php
// tests/seed_test_user.php

// This script ensures a specific test user exists for Playwright tests.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "Starting test user seeding...\n";

try {
    // --- Test User Data ---
    $username = 'testbuyer';
    $email = 'buyer@example.com';
    $password = 'password';
    $role = 'buyer';

    // --- Check if user exists ---
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo " -> Test user '$username' already exists. Skipping.\n";
    } else {
        echo " -> Test user '$username' not found. Creating...\n";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Prepare failed for insert: " . $conn->error);
        }
        $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($insert_stmt->execute()) {
            echo " -> Successfully created test user '$username'.\n";
        } else {
            throw new Exception("Failed to create test user: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    }
    $stmt->close();

    echo "------------------------------------\n";
    echo "Test user seeding completed successfully!\n";
    echo "------------------------------------\n";

} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>