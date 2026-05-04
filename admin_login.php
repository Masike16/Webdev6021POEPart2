<?php
/**
 * Admin Login Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Admin-specific authentication with enhanced security
 * Following PHP.net (2022) session security best practices
 * Admin authentication inspired by Vinted (2023) admin system
 */

session_start();
require_once 'DBConn.php';

$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}

// Process admin login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username)) {
        $errors['username'] = 'Admin username is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Admin password is required';
    }
    
    // If no validation errors, attempt admin login
    if (empty($errors)) {
        $db = new DBConn();
        
        // Find admin user
        $stmt = $db->prepareAndExecute(
            "SELECT user_id, full_name, email, username, password_hash, role, is_verified, delivery_address 
             FROM tblUser 
             WHERE username = ? AND role = 'admin'",
            "s",
            [$username]
        );
        
        $admin = $db->getSingleRow($stmt);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Check if admin is verified (should always be true for admin)
            if (!$admin['is_verified']) {
                $errors['general'] = 'Admin account is not verified. Contact system administrator.';
            } else {
                // Admin login successful - secure session management
                session_regenerate_id(true); // Prevent session fixation
                
                // Store admin data in session
                $_SESSION['user_id'] = $admin['user_id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['is_verified'] = $admin['is_verified'];
                $_SESSION['delivery_address'] = $admin['delivery_address'];
                $_SESSION['login_time'] = time();
                $_SESSION['is_admin'] = true; // Additional admin flag
                
                // Redirect to admin dashboard
                header('Location: admin_dashboard.php');
                exit;
            }
        } else {
            $errors['general'] = 'Invalid admin credentials';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pastimes Second-Hand Fashion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1>Pastimes</h1>
                <span class="tagline">Second-Hand Fashion</span>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="login.php">User Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card admin-login">
                <div class="admin-header">
                    <h2>🔐 Admin Login</h2>
                    <p>Administrator access only</p>
                </div>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="username">Admin Username *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               class="<?php echo isset($errors['username']) ? 'error' : ''; ?>"
                               required autofocus>
                        <?php if (isset($errors['username'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['username']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Admin Password *</label>
                        <input type="password" id="password" name="password" 
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">Admin Login</button>
                </form>
                
                <div class="auth-links">
                    <p>Regular user? <a href="login.php">User Login</a></p>
                    <p>Need an account? <a href="register.php">Register here</a></p>
                </div>
                
                <!-- Default Admin Credentials -->
                <div class="admin-credentials">
                    <h3>Default Admin Credentials</h3>
                    <div class="credential-info">
                        <strong>Username:</strong> admin<br>
                        <strong>Password:</strong> Admin123!
                    </div>
                    <p class="security-note">
                        ⚠️ For security, change the default password after first login.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; 2024 Pastimes Second-Hand Fashion. All rights reserved.</p>
            <p>Student Project: ST10451774 & ST10452404</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
?>
