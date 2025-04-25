<?php
// Ensure session is started *before* any output or session access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();

// Get flash messages if they exist
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']); // Clear them

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Member - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <!-- Navigation Bar (Consistent with other admin pages) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><i class="fas fa-users-cog me-2"></i><?php echo SITE_NAME; ?> Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-1"></i>Public Site</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/member_add.php"><i class="fas fa-user-plus me-1"></i>Add Member</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/registration_codes.php"><i class="fas fa-key me-1"></i>Registration Codes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout (<?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-user-plus me-2"></i>Add New Member</h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form points to the processing script -->
        <form method="POST" action="<?php echo BASE_URL; ?>admin/process/member_add_process.php" enctype="multipart/form-data">

            <!-- Member Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-user-circle me-2"></i>Member Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="member_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control" id="member_photo" name="member_photo" accept="image/jpeg, image/png, image/gif">
                            <small class="form-text text-muted">Optional. Max 2MB. JPG, PNG, GIF.</small>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                         <small class="form-text text-muted">Set an initial password for the member.</small>
                    </div>
                </div>
            </div>

            <!-- Family Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-users me-2"></i>Family Details</h4>
                </div>
                <div class="card-body">
                    <div id="family-members-container">
                        <!-- Family member template will be added here by JS -->
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" onclick="addFamilyMember()"><i class="fas fa-plus me-1"></i>Add Family Member</button>
                    <small class="form-text text-muted d-block mt-1">Add spouse or children details.</small>
                </div>
            </div>

            <!-- Business Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h4><i class="fas fa-briefcase me-2"></i>Business Details</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="business_name" class="form-label">Business Name</label>
                        <input type="text" class="form-control" id="business_name" name="business_name">
                    </div>
                    <div class="mb-3">
                        <label for="business_description" class="form-label">Business Details</label>
                        <textarea class="form-control" id="business_description" name="business_description" rows="4"
                                  placeholder="Enter business description, address, contact info, etc."></textarea>
                    </div>
                     <div class="mb-3">
                        <label for="business_photo" class="form-label">Business Photo/Logo</label>
                        <input type="file" class="form-control" id="business_photo" name="business_photo" accept="image/jpeg, image/png, image/gif">
                        <small class="form-text text-muted">Optional. Max 2MB. JPG, PNG, GIF.</small>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="<?php echo BASE_URL; ?>admin/" class="btn btn-secondary me-2"><i class="fas fa-times me-1"></i>Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Add Member</button>
            </div>
        </form>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let familyMemberIndex = 0;

        function toggleChildFields(selectElement) {
            const familyMemberDiv = selectElement.closest('.family-member-entry');
            const childFields = familyMemberDiv.querySelectorAll('.child-fields');
            const displayStyle = selectElement.value === 'child' ? 'block' : 'none';
            childFields.forEach(field => {
                field.style.display = displayStyle;
                // Make fields required only if child is selected and visible
                const inputs = field.querySelectorAll('input');
                // inputs.forEach(input => input.required = (displayStyle === 'block')); // Removed required for DOB/Edu
            });
        }

        function addFamilyMember() {
            const container = document.getElementById('family-members-container');
            const newMemberDiv = document.createElement('div');
            newMemberDiv.className = 'family-member-entry border rounded p-3 mb-3';
            // Use unique IDs for labels and inputs
            const currentIndex = familyMemberIndex;
            newMemberDiv.innerHTML = `
                <h5>Family Member ${currentIndex + 1} <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="removeFamilyMember(this)"><i class="fas fa-trash-alt"></i></button></h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="family_name_${currentIndex}" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="family_name_${currentIndex}" name="family_name[${currentIndex}]" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="family_relation_${currentIndex}" class="form-label">Relation <span class="text-danger">*</span></label>
                        <select class="form-select relation-type" id="family_relation_${currentIndex}" name="family_relation[${currentIndex}]" onchange="toggleChildFields(this)" required>
                            <option value="" selected disabled>-- Select --</option>
                            <option value="spouse">Spouse</option>
                            <option value="child">Child</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="family_photo_${currentIndex}" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="family_photo_${currentIndex}" name="family_photo[${currentIndex}]" accept="image/jpeg, image/png, image/gif">
                         <small class="form-text text-muted">Optional.</small>
                    </div>
                </div>
                <div class="row child-fields" style="display: none;">
                     <div class="col-md-6 mb-3">
                        <label for="family_dob_${currentIndex}" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="family_dob_${currentIndex}" name="family_dob[${currentIndex}]" max="${new Date().toISOString().split('T')[0]}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="family_education_${currentIndex}" class="form-label">Education</label>
                        <input type="text" class="form-control" id="family_education_${currentIndex}" name="family_education[${currentIndex}]" placeholder="e.g., Grade 5, High School">
                    </div>
                </div>
            `;
            container.appendChild(newMemberDiv);
            familyMemberIndex++; // Increment index for the next member
        }

        function removeFamilyMember(button) {
             const entry = button.closest('.family-member-entry');
             entry.remove();
             // Optional: Renumber headers if needed, though usually not critical
        }

        // Add the first family member entry automatically on page load if desired
        // addFamilyMember(); // Uncomment this line to have one entry by default
    </script>
</body>
</html>