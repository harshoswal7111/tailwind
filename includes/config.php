<?php
session_start();

// Define base URL
define('BASE_URL', 'http://localhost/tailwind/'); // Adjust if necessary

// Define file paths for data storage
define('DATA_DIR', __DIR__ . '/../data/');
define('MEMBERS_FILE', DATA_DIR . 'members.json');
define('CODES_FILE', DATA_DIR . 'codes.json');
define('ADMINS_FILE', DATA_DIR . 'admins.json');

// Define upload directories
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('MEMBER_UPLOADS', UPLOADS_DIR . 'members/');
define('FAMILY_UPLOADS', UPLOADS_DIR . 'family/');
define('BUSINESS_UPLOADS', UPLOADS_DIR . 'businesses/');

// Ensure upload directories exist
if (!is_dir(MEMBER_UPLOADS)) {
    mkdir(MEMBER_UPLOADS, 0777, true);
}
if (!is_dir(FAMILY_UPLOADS)) {
    mkdir(FAMILY_UPLOADS, 0777, true);
}
if (!is_dir(BUSINESS_UPLOADS)) {
    mkdir(BUSINESS_UPLOADS, 0777, true);
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>