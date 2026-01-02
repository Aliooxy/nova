<?php
require_once '../../src/includes/session.php';
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = "CSRF token validation failed.";
        header("Location: ../../views/auth/login.php");
        exit();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required.";
        header("Location: ../../views/auth/login.php");
        exit();
    }

    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username, $hashed_password, $role);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct, so start a new session
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["role"] = $role;

                        // Redirect user to welcome page based on role
                        switch($role) {
                            case 'admin':
                                header("location: ../../admin_dashboard.php");
                                break;
                            case 'seller':
                                header("location: ../../seller_dashboard.php");
                                break;
                            case 'buyer':
                                header("location: ../../buyer_dashboard.php");
                                break;
                            default:
                                header("location: ../../index.php");
                        }
                        exit();
                    } else {
                        // Display an error message if password is not valid
                        $_SESSION['error'] = "The password you entered was not valid.";
                        header("location: ../../views/auth/login.php");
                        exit();
                    }
                }
            } else {
                // Display an error message if username doesn't exist
                $_SESSION['error'] = "No account found with that username.";
                header("location: ../../views/auth/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Oops! Something went wrong. Please try again later.";
            header("location: ../../views/auth/login.php");
            exit();
        }
        $stmt->close();
    }
}
$conn->close();
?>