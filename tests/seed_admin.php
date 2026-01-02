<?php
// tests/seed_admin.php

// This script securely creates a default admin user.
// Run this once from the command line to set up your administrator account.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';

echo "Starting admin user seeding...\n";

try {
    // --- Admin User Data ---
    // You can change these credentials if you wish.
    $username = 'admin';
    $email = 'admin@example.com';
    $password = 'admin_password'; // Strong password recommended for production
    $role = 'admin';

    // --- Check if admin user already exists ---
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    if (!$stmt_check) {
        throw new Exception("Prepare failed for check: " . $conn->error);
    }
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo " -> An admin user with username '$username' or email '$email' already exists. Skipping.\n";
        $stmt_check->close();
    } else {
        $stmt_check->close();
        echo " -> Admin user not found. Creating...\n";

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the insert statement
        $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        if (!$stmt_insert) {
            throw new Exception("Prepare failed for insert: " . $conn->error);
        }
        $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);

        // Execute the statement
        if ($stmt_insert->execute()) {
            echo " -> Successfully created admin user '$username'.\n";
            echo "    Username: " . $username . "\n";
            echo "    Password: " . $password . "\n";
        } else {
            throw new Exception("Failed to create admin user: " . $stmt_insert->error);
        }
        $stmt_insert->close();
    }

    echo "------------------------------------\n";
    echo "Admin user seeding completed successfully!\n";
    echo "------------------------------------\n";

} catch (Exception $e) {
    echo "An error occurred during admin seeding: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
?>
