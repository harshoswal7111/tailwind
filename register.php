<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
$error = '';
$registration_code = isset($_GET['code']) ? sanitizeInput($_GET['code']) : '';
$code_validated = false;
$code_data = null;

// Validate the registration code if provided
if (!empty($registration_code)) {
    $code_data = validateRegistrationCode($registration_code); // Uses JSON function
    if ($code_data) {
        $code_validated = true;
    } else {
        $error = 'Invalid, expired, or already used registration code. Please contact the administrator.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $code_validated) {
    // Ensure the code submitted in the form matches the validated one
    $submitted_code = isset($_POST['registration_code']) ? sanitizeInput($_POST['registration_code']) : '';
    if ($submitted_code !== $registration_code) {
        $error = "Registration code mismatch. Please try again.";
        $code_validated = false; // Re-invalidate if codes don't match
    } else {
        try {
            // --- Sanitize ALL inputs --- 
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $contact = sanitizeInput($_POST['contact'] ?? '');
            $address = sanitizeInput($_POST['address'] ?? '');
            $bio = sanitizeInput($_POST['bio'] ?? ''); // Added Bio field
            $dob = sanitizeInput($_POST['date_of_birth'] ?? ''); // Added DOB field

            $family_names = isset($_POST['family_name']) ? sanitizeInput($_POST['family_name']) : [];
            $family_relations = isset($_POST['family_relation']) ? sanitizeInput($_POST['family_relation']) : [];
            $family_dobs = isset($_POST['family_dob']) ? sanitizeInput($_POST['family_dob']) : [];
            $family_educations = isset($_POST['family_education']) ? sanitizeInput($_POST['family_education']) : [];
            // Note: $_FILES are handled separately by uploadImage function

            $business_name = sanitizeInput($_POST['business_name'] ?? '');
            $business_description = sanitizeInput($_POST['business_description'] ?? '');
            $business_website = sanitizeInput($_POST['business_website'] ?? ''); // Added website
            $business_contact = sanitizeInput($_POST['business_contact'] ?? ''); // Added contact
            $business_address = sanitizeInput($_POST['business_address'] ?? ''); // Added address

            // --- Basic Validation --- 
            if (empty($name) || empty($email) || empty($contact) || empty($address)) {
                throw new Exception("Please fill in all required member details (Name, Email, Contact, Address).");
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 throw new Exception("Invalid email format.");
            }

            // --- Check for existing email --- 
            if (getMemberByEmail($email) !== null) {
                throw new Exception("A member with this email address already exists.");
            }

            // --- Prepare Member Data --- 
            $memberData = [
                'name' => $name,
                'email' => $email,
                'contact' => $contact,
                'address' => $address,
                'bio' => $bio,
                'date_of_birth' => $dob,
                'profile_image' => null, // Placeholder, will be updated after upload
                'family_image' => null, // Placeholder for main family image
                'family_members' => [],
                'business_details' => null
            ];

            // --- Handle Member Photo Upload --- 
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $memberData['profile_image'] = uploadImage($_FILES['profile_image'], 'members');
            }
            
            // --- Handle Main Family Photo Upload --- 
            if (isset($_FILES['family_image']) && $_FILES['family_image']['error'] === UPLOAD_ERR_OK) {
                $memberData['family_image'] = uploadImage($_FILES['family_image'], 'family');
            }

            // --- Handle Family Members --- 
            if (is_array($family_names)) {
                foreach ($family_names as $index => $fam_name) {
                    $fam_name = trim($fam_name);
                    if (!empty($fam_name)) {
                        $relation_type = $family_relations[$index] ?? 'child'; // Default to child if not set
                        $fam_dob = ($relation_type === 'child' && !empty($family_dobs[$index])) ? $family_dobs[$index] : null;
                        $fam_education = ($relation_type === 'child' && !empty($family_educations[$index])) ? $family_educations[$index] : null;
                        
                        $familyMemberData = [
                            'name' => $fam_name,
                            'relation_type' => $relation_type,
                            'date_of_birth' => $fam_dob,
                            'education' => $fam_education,
                            // 'photo' => null // Individual family member photos removed for simplicity, add back if needed
                        ];

                        // Handle individual family member photo upload (if re-enabled)
                        /*
                        if (isset($_FILES['family_photo']['tmp_name'][$index]) && 
                            $_FILES['family_photo']['error'][$index] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['family_photo']['name'][$index],
                                'type' => $_FILES['family_photo']['type'][$index],
                                'tmp_name' => $_FILES['family_photo']['tmp_name'][$index],
                                'error' => $_FILES['family_photo']['error'][$index],
                                'size' => $_FILES['family_photo']['size'][$index]
                            ];
                            try {
                                $familyMemberData['photo'] = uploadImage($file, 'family');
                            } catch (Exception $e) {
                                // Log error but continue processing other family members
                                error_log("Failed to upload photo for family member {$fam_name}: " . $e->getMessage());
                            }
                        }
                        */

                        $memberData['family_members'][] = $familyMemberData;
                    }
                }
            }

            // --- Handle Business Details --- 
            if (!empty($business_name)) {
                 $businessDetails = [
                    'business_name' => $business_name,
                    'description' => $business_description,
                    'website' => $business_website,
                    'contact' => $business_contact,
                    'address' => $business_address,
                    'business_logo' => null // Placeholder
                 ];
                 
                 // Handle Business Logo Upload
                 if (isset($_FILES['business_logo']) && $_FILES['business_logo']['error'] === UPLOAD_ERR_OK) {
                    $businessDetails['business_logo'] = uploadImage($_FILES['business_logo'], 'businesses');
                 }
                 $memberData['business_details'] = $businessDetails;
            }

            // --- Add Member to JSON --- 
            $newMemberId = addMember($memberData);

            if ($newMemberId === false) {
                throw new Exception("Failed to save registration data. Please try again later.");
            }

            // --- Mark Registration Code as Used --- 
            if (!markRegistrationCodeAsUsed($registration_code, $newMemberId)) {
                // Log this error, but the member was likely created. Manual cleanup might be needed.
                error_log("CRITICAL: Failed to mark registration code {$registration_code} as used for member ID {$newMemberId}.");
                // Optionally inform the user, but avoid causing undue alarm if registration mostly succeeded.
                $success = 'Registration completed successfully! Your information has been submitted. (Note: Code update issue occurred, please inform admin)';
            } else {
                 $success = 'Registration completed successfully! Your information has been submitted.';
            }

        } catch (Exception $e) {
            $error = "Registration failed: " . $e->getMessage();
            // Clean up uploaded files if registration failed mid-way
            if (!empty($memberData['profile_image']) && file_exists(MEMBER_UPLOADS . $memberData['profile_image'])) {
                @unlink(MEMBER_UPLOADS . $memberData['profile_image']);
            }
             if (!empty($memberData['family_image']) && file_exists(FAMILY_UPLOADS . $memberData['family_image'])) {
                @unlink(FAMILY_UPLOADS . $memberData['family_image']);
            }
            if (isset($memberData['business_details']['business_logo']) && !empty($memberData['business_details']['business_logo']) && file_exists(BUSINESS_UPLOADS . $memberData['business_details']['business_logo'])) {
                @unlink(BUSINESS_UPLOADS . $memberData['business_details']['business_logo']);
            }
            // Note: Cleaning up individual family member photos would require iterating $memberData['family_members'] if that feature was enabled.
        }
    }
}

// Include header here, after potential redirects or logic
require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <h2 class="mb-4 text-center">Register as a New Member</h2>

            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <h4><i class="fas fa-check-circle me-2"></i>Thank You!</h4>
                    <p><?php echo $success; ?></p>
                    <p><a href="<?php echo BASE_URL; ?>" class="btn btn-outline-success mt-2">Return to Home Page</a></p>
                </div>
            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!$code_validated): ?>
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4><i class="fas fa-key me-2"></i>Enter Registration Code</h4>
                        </div>
                        <div class="card-body">
                            <p>To register as a new member, you need a valid registration code provided by an administrator. Please enter your code below:</p>
                            <form method="GET" action="">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Registration Code</label>
                                    <input type="text" class="form-control" id="code" name="code" required value="<?php echo htmlspecialchars($registration_code); ?>" autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary">Validate Code</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?code=<?php echo htmlspecialchars($registration_code); ?>" enctype="multipart/form-data" id="registrationForm">
                        <input type="hidden" name="registration_code" value="<?php echo htmlspecialchars($registration_code); ?>">

                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4><i class="fas fa-user me-2"></i>Your Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="contact" name="contact" required>
                                    </div>
                                     <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                                </div>
                                 <div class="mb-3">
                                    <label for="bio" class="form-label">Short Bio / About Me</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Optional: Tell us a bit about yourself..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Your Photo</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small class="form-text text-muted">Optional. Max 5MB. JPG, PNG, GIF, WEBP.</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4><i class="fas fa-users me-2"></i>Family Details</h4>
                            </div>
                            <div class="card-body">
                                 <div class="mb-3">
                                    <label for="family_image" class="form-label">Main Family Photo</label>
                                    <input type="file" class="form-control" id="family_image" name="family_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small class="form-text text-muted">Optional. A photo of your whole family. Max 5MB.</small>
                                </div>
                                <hr>
                                <p class="text-muted small">Add details for spouse and children below.</p>
                                <div id="family-members-container">
                                    <!-- Family member template will be inserted here by JS -->
                                </div>
                                <button type="button" class="btn btn-outline-secondary mt-2" id="add-family-member-btn"><i class="fas fa-plus me-1"></i>Add Family Member</button>
                            </div>
                        </div>

                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4><i class="fas fa-briefcase me-2"></i>Business Details (Optional)</h4>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="business_name" class="form-label">Business Name</label>
                                        <input type="text" class="form-control" id="business_name" name="business_name">
                                    </div>
                                     <div class="col-md-6">
                                        <label for="business_website" class="form-label">Website</label>
                                        <input type="url" class="form-control" id="business_website" name="business_website" placeholder="https://example.com">
                                    </div>
                                </div>
                                 <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="business_contact" class="form-label">Business Contact Phone</label>
                                        <input type="tel" class="form-control" id="business_contact" name="business_contact">
                                    </div>
                                     <div class="col-md-6">
                                        <label for="business_logo" class="form-label">Business Logo</label>
                                        <input type="file" class="form-control" id="business_logo" name="business_logo" accept="image/jpeg,image/png,image/gif,image/webp">
                                        <small class="form-text text-muted">Optional. Max 5MB.</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="business_address" class="form-label">Business Address</label>
                                    <textarea class="form-control" id="business_address" name="business_address" rows="2"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="business_description" class="form-label">Business Description</label>
                                    <textarea class="form-control" id="business_description" name="business_description" rows="4" placeholder="Describe your business or service..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-4">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check me-2"></i>Complete Registration</button>
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('family-members-container');
    const addButton = document.getElementById('add-family-member-btn');
    let memberIndex = 0;

    function createFamilyMemberFields(index) {
        const div = document.createElement('div');
        div.classList.add('family-member-entry', 'border', 'p-3', 'mb-3', 'rounded');
        div.innerHTML = `
            <h5>Family Member ${index + 1} <button type="button" class="btn btn-sm btn-outline-danger float-end remove-family-member-btn">&times;</button></h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="family_name_${index}" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="family_name_${index}" name="family_name[]" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="family_relation_${index}" class="form-label">Relation <span class="text-danger">*</span></label>
                    <select class="form-select relation-type" id="family_relation_${index}" name="family_relation[]" required data-index="${index}">
                        <option value="" selected disabled>-- Select --</option>
                        <option value="spouse">Spouse</option>
                        <option value="child">Child</option>
                    </select>
                </div>
            </div>
            <div class="row child-fields child-fields-${index}" style="display: none;">
                <div class="col-md-6 mb-3">
                    <label for="family_dob_${index}" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="family_dob_${index}" name="family_dob[]" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="family_education_${index}" class="form-label">Education/School</label>
                    <input type="text" class="form-control" id="family_education_${index}" name="family_education[]" placeholder="e.g., Grade 5, High School, College Name">
                </div>
            </div>
        `;

        // Add event listener for relation change
        const relationSelect = div.querySelector('.relation-type');
        relationSelect.addEventListener('change', handleRelationChange);

        // Add event listener for remove button
        const removeButton = div.querySelector('.remove-family-member-btn');
        removeButton.addEventListener('click', function() {
            div.remove();
            // Optional: Renumber headings if needed after removal
        });

        return div;
    }

    function handleRelationChange(event) {
        const index = event.target.dataset.index;
        const childFieldsDiv = event.target.closest('.family-member-entry').querySelector(`.child-fields-${index}`);
        if (event.target.value === 'child') {
            childFieldsDiv.style.display = 'flex'; // Use flex for row layout
        } else {
            childFieldsDiv.style.display = 'none';
            // Clear child-specific fields when relation changes from child
            childFieldsDiv.querySelectorAll('input').forEach(input => input.value = '');
        }
    }

    addButton.addEventListener('click', function() {
        container.appendChild(createFamilyMemberFields(memberIndex));
        memberIndex++;
    });

    // Add one set of fields initially
    // container.appendChild(createFamilyMemberFields(memberIndex));
    // memberIndex++;
    // Decided against adding one initially to keep it cleaner, user clicks to add.
});
</script>

</body>
</html>