<?php
require_once '../../src/includes/session.php';
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = "CSRF token validation failed.";
        header("Location: ../../views/auth/register.php");
        exit();
    }

    // Sanitize user input
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Will be hashed, no need to sanitize string
    $role = $_POST['role'];

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../../views/auth/register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../../views/auth/register.php");
        exit();
    }

    // Ensure role is either buyer or seller, not admin
    if (!in_array($role, ['buyer', 'seller'])) {
        $_SESSION['error'] = "Invalid role selected.";
        header("Location: ../../views/auth/register.php");
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare an insert statement
    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssss", $param_username, $param_email, $param_password, $param_role);

        // Set parameters
        $param_username = $username;
        $param_email = $email;
        $param_password = $hashed_password;
        $param_role = $role;

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: ../../views/auth/login.php");
            exit();
        } else {
             // Check for duplicate entry
            if ($stmt->errno == 1062) {
                $_SESSION['error'] = "This username or email is already taken.";
            } else {
                $_SESSION['error'] = "Something went wrong. Please try again later.";
            }
            header("Location: ../../views/auth/register.php");
            exit();
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>