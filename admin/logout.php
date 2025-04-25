<?php
require_once '../includes/config.php';
// No need for functions.php if only destroying session

// Start the session if not already started (required for session_destroy)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to login page with a logged-out message
header('Location: ' . BASE_URL . 'admin/login.php?logged_out=true');
exit();