<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin(); // Ensures only logged-in admins can access

$members = getAllMembers(); // Reads from members.json
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
</head>
<body>
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
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/member_add.php"><i class="fas fa-user-plus me-1"></i>Add Member</a>
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
        <h2 class="mb-4"><i class="fas fa-users me-2"></i>Member Management</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Current Members (<?php echo count($members); ?>)</span>
                <a href="<?php echo BASE_URL; ?>admin/member_add.php" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i>Add New Member</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover admin-table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No members found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $profileImagePath = BASE_URL . 'images/placeholder_profile.png'; // Default
                                            if (!empty($member['profile_image'])) {
                                                $potentialPath = MEMBER_UPLOADS . $member['profile_image'];
                                                if (file_exists($potentialPath)) {
                                                    $profileImagePath = BASE_URL . 'uploads/members/' . $member['profile_image'];
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($profileImagePath); ?>" 
                                                 alt="<?php echo htmlspecialchars($member['name'] ?? 'Member'); ?>" 
                                                 class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                        </td>
                                        <td><?php echo htmlspecialchars($member['name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($member['contact'] ?? 'N/A'); ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo BASE_URL; ?>profile.php?id=<?php echo $member['member_id']; ?>" 
                                               class="btn btn-sm btn-outline-secondary me-1" title="View Profile"><i class="fas fa-eye"></i></a>
                                            <a href="<?php echo BASE_URL; ?>admin/member_edit.php?id=<?php echo $member['member_id']; ?>" 
                                               class="btn btn-sm btn-primary me-1" title="Edit Member"><i class="fas fa-edit"></i></a>
                                            <a href="<?php echo BASE_URL; ?>admin/process/member_delete.php?id=<?php echo $member['member_id']; ?>" 
                                               class="btn btn-sm btn-danger" title="Delete Member" 
                                               onclick="return confirm('Are you sure you want to permanently delete this member and all associated data? This cannot be undone.')"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>