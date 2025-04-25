<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$members = getAllMembers(); // This function now reads from members.json
?>

<div class="container mt-4">
    <h1 class="mb-4 text-center">Member Directory</h1>
    <div class="row g-4 py-5">
        <?php if (empty($members)): ?>
            <div class="col-12">
                <p class="text-center text-muted">No members found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($members as $member): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card member-card h-100 shadow-sm">
                        <?php 
                        // Construct image path using constants defined in config.php
                        $profileImagePath = BASE_URL . 'uploads/members/' . ($member['profile_image'] ?? 'default.png'); 
                        // Check if the actual file exists, otherwise use a default placeholder
                        $profileImageServerPath = MEMBER_UPLOADS . ($member['profile_image'] ?? '');
                        if (empty($member['profile_image']) || !file_exists($profileImageServerPath)) {
                            // You might want a placeholder image in uploads/members/default.png
                            $profileImagePath = BASE_URL . 'images/placeholder_profile.png'; // Example placeholder path
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($profileImagePath); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($member['name'] ?? 'Member'); ?>" 
                             style="height: 250px; object-fit: cover;"> 
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($member['name'] ?? 'N/A'); ?></h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($member['bio'] ?? 'No bio available.', 0, 100)); ?>
                                <?php if (isset($member['bio']) && strlen($member['bio']) > 100) echo '...'; ?>
                            </p>
                            <a href="<?php echo BASE_URL; ?>profile.php?id=<?php echo $member['member_id']; ?>" 
                               class="btn btn-primary mt-auto align-self-start">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>