<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdminLogin();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Basic Member Details ---
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- Business Details ---
    $business_name = trim($_POST['business_name'] ?? '');
    $business_description = trim($_POST['business_description'] ?? '');

    // --- File Upload Paths ---
    $member_photo_path = '';
    $business_photo_path = '';
    $family_photos_paths = [];

    // --- Validation ---
    if (empty($name) || empty($email) || empty($contact) || empty($address) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all required member fields (Name, Email, Contact, Address, Password).";
        header("Location: " . BASE_URL . "admin/member_add.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: " . BASE_URL . "admin/member_add.php");
        exit;
    }

    // --- Load Existing Data ---
    $members = loadMembers();

    // --- Check for Duplicate Email ---
    if (isEmailRegistered($email, $members)) {
        $_SESSION['error_message'] = "This email address is already registered.";
        header("Location: " . BASE_URL . "admin/member_add.php");
        exit;
    }

    // --- Handle Member Photo Upload ---
    if (isset($_FILES['member_photo']) && $_FILES['member_photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/members/';
        $result = handleFileUpload($_FILES['member_photo'], $upload_dir);
        if ($result['success']) {
            $member_photo_path = 'uploads/members/' . $result['filename']; // Relative path for storage
        } else {
            $_SESSION['error_message'] = "Member photo upload failed: " . $result['message'];
            header("Location: " . BASE_URL . "admin/member_add.php");
            exit;
        }
    }

    // --- Handle Business Photo Upload ---
    if (isset($_FILES['business_photo']) && $_FILES['business_photo']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/businesses/';
        $result = handleFileUpload($_FILES['business_photo'], $upload_dir);
        if ($result['success']) {
            $business_photo_path = 'uploads/businesses/' . $result['filename']; // Relative path for storage
        } else {
            $_SESSION['error_message'] = "Business photo upload failed: " . $result['message'];
            // Optionally delete member photo if it was uploaded in the same request
            if ($member_photo_path && file_exists('../../' . $member_photo_path)) {
                unlink('../../' . $member_photo_path);
            }
            header("Location: " . BASE_URL . "admin/member_add.php");
            exit;
        }
    }

    // --- Process Family Members ---
    $family_members = [];
    if (isset($_POST['family_name']) && is_array($_POST['family_name'])) {
        $family_upload_dir = '../../uploads/family/';
        foreach ($_POST['family_name'] as $index => $fam_name) {
            $fam_name = trim($fam_name);
            $fam_relation = trim($_POST['family_relation'][$index] ?? '');
            $fam_dob = trim($_POST['family_dob'][$index] ?? '');
            $fam_education = trim($_POST['family_education'][$index] ?? '');
            $fam_photo_path = '';

            // Validate required fields for family member
            if (empty($fam_name) || empty($fam_relation)) {
                 $_SESSION['error_message'] = "Family member name and relation are required for all entries.";
                 // Clean up previously uploaded files before redirecting
                 if ($member_photo_path && file_exists('../../' . $member_photo_path)) unlink('../../' . $member_photo_path);
                 if ($business_photo_path && file_exists('../../' . $business_photo_path)) unlink('../../' . $business_photo_path);
                 foreach($family_photos_paths as $path) { if ($path && file_exists('../../' . $path)) unlink('../../' . $path); }
                 header("Location: " . BASE_URL . "admin/member_add.php");
                 exit;
            }

            // Handle Family Photo Upload
            if (isset($_FILES['family_photo']) && isset($_FILES['family_photo']['error'][$index]) && $_FILES['family_photo']['error'][$index] == UPLOAD_ERR_OK) {
                // Need to restructure the $_FILES array for the function
                $family_photo_file = [
                    'name' => $_FILES['family_photo']['name'][$index],
                    'type' => $_FILES['family_photo']['type'][$index],
                    'tmp_name' => $_FILES['family_photo']['tmp_name'][$index],
                    'error' => $_FILES['family_photo']['error'][$index],
                    'size' => $_FILES['family_photo']['size'][$index]
                ];
                $result = handleFileUpload($family_photo_file, $family_upload_dir);
                if ($result['success']) {
                    $fam_photo_path = 'uploads/family/' . $result['filename']; // Relative path
                    $family_photos_paths[] = $fam_photo_path; // Keep track for potential cleanup
                } else {
                    $_SESSION['error_message'] = "Family member photo upload failed: " . $result['message'];
                    // Clean up previously uploaded files
                    if ($member_photo_path && file_exists('../../' . $member_photo_path)) unlink('../../' . $member_photo_path);
                    if ($business_photo_path && file_exists('../../' . $business_photo_path)) unlink('../../' . $business_photo_path);
                    foreach($family_photos_paths as $path) { if ($path && file_exists('../../' . $path)) unlink('../../' . $path); }
                    header("Location: " . BASE_URL . "admin/member_add.php");
                    exit;
                }
            }

            $family_member_data = [
                'name' => $fam_name,
                'relation' => $fam_relation,
                'photo' => $fam_photo_path
            ];

            // Add child-specific fields if relation is 'child'
            if ($fam_relation === 'child') {
                $family_member_data['dob'] = $fam_dob; // Can be empty
                $family_member_data['education'] = $fam_education; // Can be empty
            }

            $family_members[] = $family_member_data;
        }
    }

    // --- Prepare New Member Data ---
    $member_id = generateUniqueId(); // Assuming you have this function in functions.php
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $new_member = [
        'id' => $member_id,
        'name' => $name,
        'email' => $email,
        'contact' => $contact,
        'address' => $address,
        'password' => $hashed_password,
        'photo' => $member_photo_path, // Store relative path
        'family_members' => $family_members,
        'business_details' => [
            'name' => $business_name,
            'description' => $business_description,
            'photo' => $business_photo_path // Store relative path
        ],
        'registration_date' => date('Y-m-d H:i:s'),
        'status' => 'active' // Default status
    ];

    // --- Add to Members Array and Save ---
    $members[] = $new_member;
    if (saveMembers($members)) {
        $_SESSION['success_message'] = "Member '$name' added successfully!";
        header("Location: " . BASE_URL . "admin/"); // Redirect to admin dashboard
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to save member data. Please check file permissions.";
        // Clean up uploaded files as saving failed
        if ($member_photo_path && file_exists('../../' . $member_photo_path)) unlink('../../' . $member_photo_path);
        if ($business_photo_path && file_exists('../../' . $business_photo_path)) unlink('../../' . $business_photo_path);
        foreach($family_photos_paths as $path) { if ($path && file_exists('../../' . $path)) unlink('../../' . $path); }
        header("Location: " . BASE_URL . "admin/member_add.php");
        exit;
    }

} else {
    // Redirect if accessed directly without POST
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: " . BASE_URL . "admin/member_add.php");
    exit;
}
?>