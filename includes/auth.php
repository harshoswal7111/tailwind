<?php
session_start();

class Auth {
    private $fileDB;
    
    public function __construct() {
        $this->fileDB = new FileDB();
    }

    public function login($username, $password) {
        // For production: Store hashed password in secure config
        $adminUser = 'admin';
        $adminPassHash = password_hash('securepassword123', PASSWORD_DEFAULT);
        
        if ($username === $adminUser && password_verify($password, $adminPassHash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function getCSRFToken() {
        return $_SESSION['csrf_token'] ?? '';
    }
}