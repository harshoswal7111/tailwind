<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    // Redirect to intended page or admin dashboard
    $redirect_url = $_SESSION['redirect_url'] ?? BASE_URL . 'admin/';
    unset($_SESSION['redirect_url']); // Clear the stored URL
    header('Location: ' . $redirect_url);
    exit();
}

$error = '';
$username_attempt = ''; // Keep username in field after failed attempt

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize password before verification
    $username_attempt = $username; // Store for repopulating field

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $admin = getAdminByUsername($username); // Reads from admins.json

            if ($admin && isset($admin['password_hash']) && verifyPassword($password, $admin['password_hash'])) {
                // Password is correct, set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to intended page or admin dashboard
                $redirect_url = $_SESSION['redirect_url'] ?? BASE_URL . 'admin/';
                unset($_SESSION['redirect_url']); // Clear the stored URL
                header('Location: ' . $redirect_url);
                exit();
            } else {
                // Invalid username or password
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            // Catch potential file read/decode errors from functions.php
            error_log("Admin login error: " . $e->getMessage());
            $error = 'An unexpected error occurred. Please try again later.';
        }
    }
}

// Check for logged_out message
$logged_out_message = '';
if (isset($_GET['logged_out']) && $_GET['logged_out'] === 'true') {
    $logged_out_message = 'You have been successfully logged out.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> 
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css"> 
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            max-width: 400px;
            margin: 5rem auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="text-center mb-0"><i class="fas fa-user-shield me-2"></i>Admin Login</h3>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($logged_out_message): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $logged_out_message; ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username_attempt); ?>" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                             <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center text-muted small">
                <a href="<?php echo BASE_URL; ?>">Back to Main Site</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>