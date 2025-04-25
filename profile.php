<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Redirect if ID is missing or not an integer
    header('Location: ' . BASE_URL);
    exit();
}

$member_id = (int)$_GET['id'];
$member = getMemberById($member_id); // Use new function

if (!$member) {
    // Redirect if member not found
    $_SESSION['error_message'] = "Member not found."; // Optional: Add a message
    header('Location: ' . BASE_URL);
    exit();
}

// Family and Business details are now expected to be part of the $member array
$family = $member['family_members'] ?? [];
$business = $member['business_details'] ?? null;

// Sort family members (moved from functions.php for clarity, could be kept there)
usort($family, function($a, $b) {
    $order = ['spouse' => 1, 'child' => 2];
    $aOrder = $order[$a['relation_type'] ?? 'child'] ?? 99;
    $bOrder = $order[$b['relation_type'] ?? 'child'] ?? 99;
    if ($aOrder != $bOrder) {
        return $aOrder <=> $bOrder;
    }
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
});


// Helper function to calculate age (can remain here or move to functions.php if used elsewhere)
function calculateAge($dob) {
    if (!$dob || $dob === '0000-00-00') return null;
    try {
        $birth = new DateTime($dob);
        $today = new DateTime();
        // Check if birth date is in the future
        if ($birth > $today) return null;
        $age = $birth->diff($today);
        return $age->y;
    } catch (Exception $e) {
        // Handle invalid date format
        error_log("Error calculating age for DOB: {$dob} - " . $e->getMessage());
        return null;
    }
}

// Helper function to format date (can remain here or move to functions.php)
function formatDate($date) {
    if (!$date || $date === '0000-00-00') return null;
    try {
        return date('d/m/Y', strtotime($date));
    } catch (Exception $e) {
        error_log("Error formatting date: {$date} - " . $e->getMessage());
        return null;
    }
}

// --- Image Path Generation ---
$profileImagePath = BASE_URL . 'images/placeholder_profile.png'; // Default placeholder
if (!empty($member['profile_image'])) {
    $potentialPath = MEMBER_UPLOADS . $member['profile_image'];
    if (file_exists($potentialPath)) {
        $profileImagePath = BASE_URL . 'uploads/members/' . $member['profile_image'];
    }
}

$familyImagePath = BASE_URL . 'images/placeholder_family.png'; // Default placeholder
if (!empty($member['family_image'])) { // Assuming family image is stored with the main member
    $potentialPath = FAMILY_UPLOADS . $member['family_image'];
    if (file_exists($potentialPath)) {
        $familyImagePath = BASE_URL . 'uploads/family/' . $member['family_image'];
    }
}

$businessLogoPath = BASE_URL . 'images/placeholder_business.png'; // Default placeholder
if ($business && !empty($business['business_logo'])) {
    $potentialPath = BUSINESS_UPLOADS . $business['business_logo'];
    if (file_exists($potentialPath)) {
        $businessLogoPath = BASE_URL . 'uploads/businesses/' . $business['business_logo'];
    }
}

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-sm profile-card">
        <div class="card-header bg-light p-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <img src="<?php echo htmlspecialchars($profileImagePath); ?>" 
                         class="img-fluid rounded-circle profile-pic shadow-sm" alt="<?php echo htmlspecialchars($member['name'] ?? 'Member'); ?>">
                </div>
                <div class="col-md-9">
                    <h1 class="display-5 mb-1"><?php echo htmlspecialchars($member['name'] ?? 'N/A'); ?></h1>
                    <p class="lead text-muted mb-2"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></p>
                    <p class="mb-1"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($member['contact'] ?? 'N/A'); ?></p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($member['address'] ?? 'N/A'); ?></p>
                    <?php if (isset($member['date_of_birth']) && $member['date_of_birth'] !== '0000-00-00'): ?>
                        <p class="text-muted small mt-2 mb-0">Born: <?php echo formatDate($member['date_of_birth']); ?> (Age: <?php echo calculateAge($member['date_of_birth']) ?? 'N/A'; ?>)</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <?php if (!empty($member['bio'])): ?>
            <div class="profile-section mb-4">
                <h4 class="section-title"><i class="fas fa-user-circle me-2"></i>About Me</h4>
                <p><?php echo nl2br(htmlspecialchars($member['bio'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($family): ?>
            <div class="profile-section mb-4">
                <h4 class="section-title"><i class="fas fa-users me-2"></i>Family Details</h4>
                 <?php if (!empty($member['family_image'])): ?>
                    <div class="text-center mb-4">
                         <img src="<?php echo htmlspecialchars($familyImagePath); ?>" 
                              class="img-fluid rounded shadow-sm family-pic" alt="Family Photo" style="max-height: 300px;">
                    </div>
                 <?php endif; ?>
                <div class="row g-3">
                    <?php foreach ($family as $family_member): ?>
                        <div class="col-md-6">
                            <div class="card family-member-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($family_member['name'] ?? 'N/A'); ?></h5>
                                    <p class="card-subtitle text-muted mb-2"><?php echo ucfirst(htmlspecialchars($family_member['relation_type'] ?? 'N/A')); ?></p>
                                    <?php if (($family_member['relation_type'] ?? '') === 'child'): ?>
                                        <?php $dob = $family_member['date_of_birth'] ?? null; ?>
                                        <?php if ($dob && $dob !== '0000-00-00'): ?>
                                            <?php $age = calculateAge($dob); ?>
                                            <p class="card-text small mb-1">
                                                <strong>DOB:</strong> <?php echo formatDate($dob); ?>
                                                <?php if ($age !== null): ?> (<strong>Age:</strong> <?php echo $age; ?>)<?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($family_member['education'])): ?>
                                            <p class="card-text small mb-0">
                                                <strong>Education:</strong> <?php echo htmlspecialchars($family_member['education']); ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($business): ?>
            <div class="profile-section">
                <h4 class="section-title"><i class="fas fa-briefcase me-2"></i>Business Information</h4>
                <div class="row g-3 align-items-center">
                     <?php if (!empty($business['business_logo'])): ?>
                        <div class="col-md-3 text-center">
                             <img src="<?php echo htmlspecialchars($businessLogoPath); ?>" 
                                  class="img-fluid rounded business-logo mb-3 mb-md-0" alt="<?php echo htmlspecialchars($business['business_name'] ?? 'Business Logo'); ?>">
                        </div>
                        <div class="col-md-9">
                     <?php else: ?>
                        <div class="col-12">
                     <?php endif; ?>
                            <h5 class="mb-1"><?php echo htmlspecialchars($business['business_name'] ?? 'N/A'); ?></h5>
                            <?php if (!empty($business['website'])): ?>
                                <p class="mb-1"><i class="fas fa-globe me-1"></i> <a href="<?php echo htmlspecialchars($business['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($business['website']); ?></a></p>
                            <?php endif; ?>
                             <?php if (!empty($business['contact'])): ?>
                                <p class="mb-1"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($business['contact']); ?></p>
                            <?php endif; ?>
                             <?php if (!empty($business['address'])): ?>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($business['address']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($business['description'])): ?>
                                <p class="mb-0 small text-muted"><?php echo nl2br(htmlspecialchars($business['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                </div>
            </div>
            <?php endif; ?>

        </div> <!-- /card-body -->

        <div class="card-footer text-muted text-center small p-3">
            Profile last updated: <?php echo isset($member['updated_at']) ? date('F j, Y, g:i a', strtotime($member['updated_at'])) : 'N/A'; ?>
        </div>
    </div> <!-- /profile-card -->
</div> <!-- /container -->

<?php require_once 'includes/footer.php'; ?>