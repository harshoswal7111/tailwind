<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();

$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']); // Clear flash messages

// Handle code generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_code'])) {
    try {
        $expires_days = isset($_POST['expires_days']) ? intval($_POST['expires_days']) : 7;
        $admin_username = $_SESSION['admin_username'] ?? 'unknown'; // Get username from session
        
        $code = createRegistrationCode($admin_username, $expires_days); // Uses JSON function
        
        if ($code) {
            $_SESSION['success_message'] = "Registration code generated successfully: <strong>" . htmlspecialchars($code) . "</strong>";
        } else {
            $_SESSION['error_message'] = "Failed to generate registration code. Please check file permissions or logs.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error generating code: " . $e->getMessage();
    }
    header('Location: ' . BASE_URL . 'admin/registration_codes.php'); // Redirect to avoid re-submission
    exit();
}

// Handle code deactivation
if (isset($_GET['deactivate'])) {
    try {
        $code_to_deactivate = sanitizeInput($_GET['deactivate']);
        if (deactivateRegistrationCode($code_to_deactivate)) { // Uses JSON function
            $_SESSION['success_message'] = "Registration code '" . htmlspecialchars($code_to_deactivate) . "' deactivated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to deactivate code '" . htmlspecialchars($code_to_deactivate) . "'. It might not exist or is already inactive.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deactivating code: " . $e->getMessage();
    }
    header('Location: ' . BASE_URL . 'admin/registration_codes.php'); // Redirect
    exit();
}

// Handle code deletion
if (isset($_GET['delete'])) {
    try {
        $code_to_delete = sanitizeInput($_GET['delete']);
        if (deleteRegistrationCode($code_to_delete)) { // Uses JSON function
            $_SESSION['success_message'] = "Registration code '" . htmlspecialchars($code_to_delete) . "' deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete code '" . htmlspecialchars($code_to_delete) . "'. It might not exist.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error deleting code: " . $e->getMessage();
    }
    header('Location: ' . BASE_URL . 'admin/registration_codes.php'); // Redirect
    exit();
}

// Get all registration codes
$registration_codes = getRegistrationCodes(); // Reads from codes.json
$admins = getAllAdmins(); // Needed to display admin usernames
$members = getAllMembers(); // Needed to display member names

// Create lookup maps for efficiency
$admin_usernames = array_column($admins, 'username', 'admin_id'); // Assumes admin_id exists, adjust if not
$member_names = array_column($members, 'name', 'member_id');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Codes - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <!-- Navigation Bar (Consistent with admin/index.php) -->
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
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/member_add.php"><i class="fas fa-user-plus me-1"></i>Add Member</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/registration_codes.php"><i class="fas fa-key me-1"></i>Registration Codes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>admin/logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout (<?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-key me-2"></i>Registration Code Management</h2>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; // Success message allows HTML (like <strong>) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Generate Code Form -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4><i class="fas fa-plus-circle me-2"></i>Generate New Code</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="expires_days" class="form-label">Expires After (days)</label>
                                <input type="number" class="form-control" id="expires_days" name="expires_days" value="7" min="1" max="365" required>
                                <small class="form-text text-muted">How long the code should be valid.</small>
                            </div>
                            <button type="submit" name="generate_code" class="btn btn-primary w-100"><i class="fas fa-cogs me-1"></i>Generate Code</button>
                        </form>
                    </div>
                </div>
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h4><i class="fas fa-info-circle me-2"></i>Instructions</h4>
                    </div>
                    <div class="card-body small">
                        <p>Generated codes allow new members to register. Share the code directly with the intended user.</p>
                        <ul>
                            <li>Each code can only be used once.</li>
                            <li>Codes expire after the set duration.</li>
                            <li>You can manually deactivate unused codes.</li>
                            <li>Deleting a code removes it permanently.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Existing Codes Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4><i class="fas fa-list-ul me-2"></i>Existing Codes</h4>
                    </div>
                    <div class="card-body">
                        <?php if (empty($registration_codes)): ?>
                            <p class="text-center text-muted">No registration codes found. Generate one using the form.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created</th>
                                            <th>Expires</th>
                                            <th>Used By (Member)</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registration_codes as $rc): ?>
                                            <?php
                                                $is_expired = $rc['expires_at'] && strtotime($rc['expires_at']) < time();
                                                $is_used = !empty($rc['used_by_member_id']);
                                                $is_active = $rc['is_active'];
                                                $status_badge = '';
                                                $status_text = '';

                                                if ($is_used) {
                                                    $status_badge = 'bg-success';
                                                    $status_text = 'Used';
                                                } elseif (!$is_active) {
                                                    $status_badge = 'bg-secondary';
                                                    $status_text = 'Inactive';
                                                } elseif ($is_expired) {
                                                    $status_badge = 'bg-warning text-dark';
                                                    $status_text = 'Expired';
                                                } else {
                                                    $status_badge = 'bg-primary';
                                                    $status_text = 'Active';
                                                }
                                                
                                                // Get names using the lookup maps
                                                $creator_name = $admin_usernames[$rc['created_by_admin_id']] ?? 'Unknown Admin'; // Adjust key if needed
                                                $member_name = $member_names[$rc['used_by_member_id']] ?? null;
                                            ?>
                                            <tr>
                                                <td><code class="user-select-all"><?php echo htmlspecialchars($rc['code']); ?></code></td>
                                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                                <td><?php echo htmlspecialchars($creator_name); ?></td>
                                                <td><?php echo date('Y-m-d', strtotime($rc['created_at'])); ?></td>
                                                <td>
                                                    <?php echo $rc['expires_at'] ? date('Y-m-d', strtotime($rc['expires_at'])) : 'Never'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($member_name): ?>
                                                        <a href="<?php echo BASE_URL . 'profile.php?id=' . $rc['used_by_member_id']; ?>" title="View Member Profile">
                                                            <?php echo htmlspecialchars($member_name); ?>
                                                        </a>
                                                        <?php if ($rc['used_at']): ?>
                                                            <br><small class="text-muted">on <?php echo date('Y-m-d', strtotime($rc['used_at'])); ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not used</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <?php if (!$is_used && $is_active && !$is_expired): ?>
                                                        <a href="?deactivate=<?php echo urlencode($rc['code']); ?>" 
                                                           class="btn btn-sm btn-warning me-1" title="Deactivate Code"
                                                           onclick="return confirm('Are you sure you want to deactivate this code? It cannot be used for registration afterwards.');">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?delete=<?php echo urlencode($rc['code']); ?>" 
                                                       class="btn btn-sm btn-danger" title="Delete Code"
                                                       onclick="return confirm('Are you sure you want to permanently delete this code? This cannot be undone.');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>