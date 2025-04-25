<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();

if (!isset($_GET['id'])) {
    header('Location: ' . SITE_URL . '/admin');
    exit();
}

$member_id = (int)$_GET['id'];
$member = getMemberDetails($member_id);
$family = getFamilyMembers($member_id);
$business = getBusinessDetails($member_id);

if (!$member) {
    header('Location: ' . SITE_URL . '/admin');
    exit();
}

$success = '';
$error = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Member</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="process/member_edit_process.php" enctype="multipart/form-data">
            <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Member Details</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="member_photo">Photo</label>
                        <?php if ($member['photo']): ?>
                            <div class="mb-2">
                                <img src="<?php echo SITE_URL; ?>/uploads/members/<?php echo $member['photo']; ?>" 
                                     alt="Current photo" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="member_photo" name="member_photo" accept="image/*">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="contact">Contact Number</label>
                        <input type="text" class="form-control" id="contact" name="contact" value="<?php echo htmlspecialchars($member['contact']); ?>" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($member['address']); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Family Details</h4>
                </div>
                <div class="card-body">
                    <div id="family-members">
                        <?php if ($family): ?>
                            <?php foreach ($family as $index => $family_member): ?>
                                <div class="family-member mb-4">
                                    <input type="hidden" name="family_id[]" value="<?php echo $family_member['family_id']; ?>">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" class="form-control" name="family_name[]" 
                                                       value="<?php echo htmlspecialchars($family_member['name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Relation</label>
                                                <select class="form-control relation-type" name="family_relation[]" onchange="toggleChildFields(this)">
                                                    <option value="spouse" <?php echo $family_member['relation_type'] === 'spouse' ? 'selected' : ''; ?>>Spouse</option>
                                                    <option value="child" <?php echo $family_member['relation_type'] === 'child' ? 'selected' : ''; ?>>Child</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Photo</label>
                                                <?php if ($family_member['photo']): ?>
                                                    <div class="mb-2">
                                                        <img src="<?php echo SITE_URL; ?>/uploads/family/<?php echo $family_member['photo']; ?>" 
                                                             alt="Current photo" style="max-width: 100px;">
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" name="family_photo[]" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="col-md-6 child-fields" style="display: <?php echo $family_member['relation_type'] === 'child' ? 'block' : 'none'; ?>;">
                                            <div class="form-group">
                                                <label>Date of Birth</label>
                                                <input type="date" class="form-control" name="family_dob[]" 
                                                       value="<?php echo $family_member['date_of_birth']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6 child-fields" style="display: <?php echo $family_member['relation_type'] === 'child' ? 'block' : 'none'; ?>;">
                                            <div class="form-group">
                                                <label>Education</label>
                                                <input type="text" class="form-control" name="family_education[]" 
                                                       value="<?php echo htmlspecialchars($family_member['education']); ?>" 
                                                       placeholder="Current education/school">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" onclick="addFamilyMember()">Add Another Family Member</button>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Business Details</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="business_name">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name" 
                               value="<?php echo $business ? htmlspecialchars($business['business_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="business_description">Business Details</label>
                        <textarea class="form-control" id="business_description" name="business_description" rows="5" 
                                  placeholder="Enter business description and address details"><?php echo $business ? htmlspecialchars($business['description']) : ''; ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Member</button>
                <a href="<?php echo SITE_URL; ?>/admin" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleChildFields(select) {
            const row = select.closest('.row');
            const childFields = row.querySelectorAll('.child-fields');
            childFields.forEach(field => {
                field.style.display = select.value === 'child' ? 'block' : 'none';
            });
        }

        function addFamilyMember() {
            const familyMembers = document.getElementById('family-members');
            const newMember = document.createElement('div');
            newMember.className = 'family-member mt-3';
            newMember.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="family_name[]">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Relation</label>
                            <select class="form-control relation-type" name="family_relation[]" onchange="toggleChildFields(this)">
                                <option value="spouse">Spouse</option>
                                <option value="child">Child</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" class="form-control" name="family_photo[]" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6 child-fields" style="display: none;">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="family_dob[]">
                        </div>
                    </div>
                    <div class="col-md-6 child-fields" style="display: none;">
                        <div class="form-group">
                            <label>Education</label>
                            <input type="text" class="form-control" name="family_education[]" placeholder="Current education/school">
                        </div>
                    </div>
                </div>
            `;
            familyMembers.appendChild(newMember);
        }
    </script>
</body>
</html> 