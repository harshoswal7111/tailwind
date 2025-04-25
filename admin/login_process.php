<?php
session_start();
include '../includes/functions.php';

// Hardcoded admin credentials for demonstration
$adminUsername = 'admin';
$adminPassword = 'password'; // In a real application, store hashed passwords

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        echo "Invalid username or password.";
    }
} else {
    header('Location: login.php');
    exit;
}
?>