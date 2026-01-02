<?php
// tests/authentication_test.php

// This is a simple procedural test to verify the core authentication logic.
// It directly interacts with the database to simulate registration and login verification.

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Adjust the path to the database configuration file.
require_once __DIR__ . '/../config/database.php';

echo "Starting authentication test...\n";

// --- Test Data ---
$test_username = 'testuser';
$test_email = 'test@example.com';
$test_password = 'password123';
$test_role = 'buyer';

function cleanup_test_user($conn, $username) {
    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
        echo "Cleaned up test user: $username\n";
    }
}

// --- Test Execution ---
$test_passed = true;

// 1. Cleanup before starting
cleanup_test_user($conn, $test_username);

// 2. Simulate Registration (Insert user directly)
echo "Step 1: Simulating user registration...\n";
$hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
$sql_insert = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt_insert->bind_param("ssss", $test_username, $test_email, $hashed_password, $test_role);

if ($stmt_insert->execute()) {
    echo " -> Registration simulation successful.\n";
} else {
    echo " -> Registration simulation FAILED: " . $stmt_insert->error . "\n";
    $test_passed = false;
}
$stmt_insert->close();

if (!$test_passed) {
    die("Test aborted due to registration failure.\n");
}

// 3. Simulate Login (Fetch user and verify password)
echo "Step 2: Simulating user login...\n";
$sql_select = "SELECT password FROM users WHERE username = ?";
$stmt_select = $conn->prepare($sql_select);
if (!$stmt_select) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt_select->bind_param("s", $test_username);
$stmt_select->execute();
$stmt_select->store_result();

if ($stmt_select->num_rows == 1) {
    $stmt_select->bind_result($db_hashed_password);
    $stmt_select->fetch();

    if (password_verify($test_password, $db_hashed_password)) {
        echo " -> Password verification successful.\n";
    } else {
        echo " -> Password verification FAILED.\n";
        $test_passed = false;
    }
} else {
    echo " -> FAILED: Could not find the test user in the database.\n";
    $test_passed = false;
}
$stmt_select->close();


// --- Test Result ---
echo "------------------------------------\n";
if ($test_passed) {
    echo "Authentication Test: PASSED\n";
} else {
    echo "Authentication Test: FAILED\n";
}
echo "------------------------------------\n";


// 4. Cleanup after test
cleanup_test_user($conn, $test_username);


// Close the database connection
$conn->close();

// Exit with status code
if (!$test_passed) {
    exit(1); // Indicate failure
}
?>