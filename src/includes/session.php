<?php
// Start the session
session_start();

/**
 * Generates and returns a CSRF token. Stores it in the session.
 *
 * @return string The generated CSRF token.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the submitted CSRF token.
 *
 * @param string $token The submitted token.
 * @return bool True if valid, false otherwise.
 */
function validate_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Token is valid for this request.
        // For enhanced security, you might want to unset it after validation
        // if your forms are single-use.
        return true;
    }
    return false;
}


/**
 * Checks if the user is logged in. If not, redirects to the login page.
 */
function check_login() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        // Set a message to inform the user why they were redirected
        $_SESSION['error'] = "Please log in to access this page.";
        header("Location: /views/auth/login.php");
        exit;
    }
}

/**
 * Checks if the user has a specific role.
 * This function assumes the user is already logged in.
 *
 * @param string $role The role to check for (e.g., 'admin', 'seller').
 */
function check_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        // Redirect to a generic page or an unauthorized page
        // For simplicity, we'll redirect to the main index page.
        $_SESSION['error'] = "You are not authorized to view this page.";
        header("Location: /index.php");
        exit;
    }
}
?>