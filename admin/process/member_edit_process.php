<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        $member_id = (int)$_POST['member_id'];
        
        // Handle member photo upload
        $member_photo = '';
        if (isset($_FILES['member_photo']) && $_FILES['member_photo']['error'] === UPLOAD_ERR_OK) {
            $member_photo = uploadImage($_FILES['member_photo'], 'members');
            
            // Update member with new photo
            $stmt = $pdo->prepare("UPDATE members SET name = ?, photo = ?, email = ?, contact = ?, address = ? WHERE member_id = ?");
            $stmt->execute([
                sanitizeInput($_POST['name']),
                $member_photo,
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['contact']),
                sanitizeInput($_POST['address']),
                $member_id
            ]);
        } else {
            // Update member without changing photo
            $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, contact = ?, address = ? WHERE member_id = ?");
            $stmt->execute([
                sanitizeInput($_POST['name']),
                sanitizeInput($_POST['email']),
                sanitizeInput($_POST['contact']),
                sanitizeInput($_POST['address']),
                $member_id
            ]);
        }
        
        // Handle family members
        if (isset($_POST['family_name']) && is_array($_POST['family_name'])) {
            foreach ($_POST['family_name'] as $index => $name) {
                if (!empty($name)) {
                    $family_photo = '';
                    if (isset($_FILES['family_photo']['tmp_name'][$index]) && 
                        $_FILES['family_photo']['error'][$index] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['family_photo']['name'][$index],
                            'type' => $_FILES['family_photo']['type'][$index],
                            'tmp_name' => $_FILES['family_photo']['tmp_name'][$index],
                            'error' => $_FILES['family_photo']['error'][$index],
                            'size' => $_FILES['family_photo']['size'][$index]
                        ];
                        $family_photo = uploadImage($file, 'family');
                    }
                    
                    // Get relation type
                    $relation_type = sanitizeInput($_POST['family_relation'][$index]);
                    
                    // Prepare date of birth and education (only for children)
                    $dob = null;
                    $education = null;
                    if ($relation_type === 'child') {
                        if (!empty($_POST['family_dob'][$index])) {
                            $dob = $_POST['family_dob'][$index];
                        }
                        if (!empty($_POST['family_education'][$index])) {
                            $education = sanitizeInput($_POST['family_education'][$index]);
                        }
                    }
                    
                    // Check if this is an existing family member or a new one
                    if (!empty($_POST['family_id'][$index])) {
                        // Update existing family member
                        if ($family_photo) {
                            $stmt = $pdo->prepare("UPDATE family_members SET name = ?, photo = ?, relation_type = ?, date_of_birth = ?, education = ? WHERE family_id = ? AND member_id = ?");
                            $stmt->execute([
                                sanitizeInput($name),
                                $family_photo,
                                $relation_type,
                                $dob,
                                $education,
                                (int)$_POST['family_id'][$index],
                                $member_id
                            ]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE family_members SET name = ?, relation_type = ?, date_of_birth = ?, education = ? WHERE family_id = ? AND member_id = ?");
                            $stmt->execute([
                                sanitizeInput($name),
                                $relation_type,
                                $dob,
                                $education,
                                (int)$_POST['family_id'][$index],
                                $member_id
                            ]);
                        }
                    } else {
                        // Insert new family member
                        $stmt = $pdo->prepare("INSERT INTO family_members (member_id, name, photo, relation_type, date_of_birth, education) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $member_id,
                            sanitizeInput($name),
                            $family_photo,
                            $relation_type,
                            $dob,
                            $education
                        ]);
                    }
                }
            }
        }
        
        // Handle business details
        if (!empty($_POST['business_name'])) {
            // Check if business details already exist
            $stmt = $pdo->prepare("SELECT business_id FROM business_details WHERE member_id = ?");
            $stmt->execute([$member_id]);
            $existing_business = $stmt->fetch();
            
            if ($existing_business) {
                // Update existing business details
                $stmt = $pdo->prepare("UPDATE business_details SET business_name = ?, description = ? WHERE member_id = ?");
                $stmt->execute([
                    sanitizeInput($_POST['business_name']),
                    sanitizeInput($_POST['business_description']),
                    $member_id
                ]);
            } else {
                // Insert new business details
                $stmt = $pdo->prepare("INSERT INTO business_details (member_id, business_name, description) VALUES (?, ?, ?)");
                $stmt->execute([
                    $member_id,
                    sanitizeInput($_POST['business_name']),
                    sanitizeInput($_POST['business_description'])
                ]);
            }
        } else {
            // If no business name is provided, delete any existing business details
            $stmt = $pdo->prepare("DELETE FROM business_details WHERE member_id = ?");
            $stmt->execute([$member_id]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        header('Location: ' . SITE_URL . '/admin?success=Member updated successfully');
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        header('Location: ' . SITE_URL . '/admin/member_edit.php?id=' . $member_id . '&error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: ' . SITE_URL . '/admin');
}
exit(); 