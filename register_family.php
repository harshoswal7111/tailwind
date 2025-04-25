<?php
require_once 'includes/FileDB.php';
require_once 'includes/auth.php';

$fileDB = new FileDB();
$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!$auth->validateCSRF($csrfToken)) {
        die('Invalid CSRF token');
    }

    $code = $_POST['code'] ?? '';
    if (!$fileDB->validateCode($code)) {
        $error = "Invalid or already used registration code";
    } else {
        $memberData = [
            'name' => htmlspecialchars($_POST['name']),
            'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            'phone' => preg_replace('/[^0-9]/', '', $_POST['phone']),
            'address' => htmlspecialchars($_POST['address']),
            'family_members' => array_map('htmlspecialchars', $_POST['family_members'] ?? []),
            'business_details' => htmlspecialchars($_POST['business_details'] ?? '')
        ];

        if ($fileDB->saveMember($memberData)) {
            $fileDB->markCodeUsed($code, $memberData['id']);
            header('Location: signup_success.php');
            exit;
        } else {
            $error = "Failed to save registration";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<h1 class="text-2xl font-bold mb-4">Family Registration</h1>
<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
<?php endif; ?>
<form method="post" class="max-w-lg">
    <input type="hidden" name="csrf_token" value="<?= $auth->getCSRFToken() ?>">
    
    <div class="mb-4">
        <label class="block text-gray-700 mb-2">Registration Code</label>
        <input type="text" name="code" required 
               class="w-full px-3 py-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 mb-2">Primary Member Name</label>
        <input type="text" name="name" required 
               class="w-full px-3 py-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email</label>
        <input type="email" name="email" required 
               class="w-full px-3 py-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 mb-2">Phone Number</label>
        <input type="tel" name="phone" required 
               class="w-full px-3 py-2 border rounded">
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 mb-2">Address</label>
        <textarea name="address" required 
               class="w-full px-3 py-2 border rounded"></textarea>
    </div>
    
    <button type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Submit Registration
    </button>
</form>
<?php include 'includes/footer.php'; ?>