<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: ' . SITE_URL . '/admin');
    exit();
}

$member_id = (int)$_GET['id'];

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete family members
    $stmt = $pdo->prepare("DELETE FROM family_members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    
    // Delete business details
    $stmt = $pdo->prepare("DELETE FROM business_details WHERE member_id = ?");
    $stmt->execute([$member_id]);
    
    // Delete member
    $stmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    
    // Commit transaction
    $pdo->commit();
    
    header('Location: ' . SITE_URL . '/admin?success=Member deleted successfully');
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    header('Location: ' . SITE_URL . '/admin?error=' . urlencode($e->getMessage()));
}
exit(); 