<?php
/**
 * User Login Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * User authentication with session management
 * Following PHP.net (2022) session security best practices
 * Login flow inspired by Vinted (2023) user authentication system
 */

session_start();
require_once 'DBConn.php';

$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username_or_email)) {
        $errors['username_or_email'] = 'Username or email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        $db = new DBConn();
        
        // Find user by username or email
        $stmt = $db->prepareAndExecute(
            "SELECT user_id, full_name, email, username, password_hash, role, is_verified, delivery_address 
             FROM tblUser 
             WHERE username = ? OR email = ?",
            "ss",
            [$username_or_email, $username_or_email]
        );
        
        $user = $db->getSingleRow($stmt);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Check if user is verified
            if (!$user['is_verified']) {
                $errors['general'] = 'Your account is pending verification. Please wait for admin approval.';
            } else {
                // Login successful - secure session management
                session_regenerate_id(true); // Prevent session fixation
                
                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_verified'] = $user['is_verified'];
                $_SESSION['delivery_address'] = $user['delivery_address'];
                $_SESSION['login_time'] = time();
                
                // Clear any existing cart data for this user
                $db->prepareAndExecute("DELETE FROM tblCart WHERE user_id = ?", "i", [$user['user_id']]);
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } elseif ($user['role'] === 'seller') {
                    header('Location: seller_dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        } else {
            $errors['general'] = 'Invalid username/email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastimes Second-Hand Fashion</title>
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
                <a href="register.php">Register</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p>Login to your Pastimes account</p>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="username_or_email">Username or Email *</label>
                        <input type="text" id="username_or_email" name="username_or_email" 
                               value="<?php echo isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : ''; ?>"
                               class="<?php echo isset($errors['username_or_email']) ? 'error' : ''; ?>"
                               required autofocus>
                        <?php if (isset($errors['username_or_email'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['username_or_email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" 
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">Login</button>
                </form>
                
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <p>Are you an administrator? <a href="admin_login.php">Admin Login</a></p>
                </div>
                
                <!-- Demo Accounts Info -->
                <div class="demo-accounts">
                    <h3>Demo Accounts</h3>
                    <div class="demo-account">
                        <strong>Admin:</strong> username: admin, password: Admin123!
                    </div>
                    <div class="demo-account">
                        <strong>Seller:</strong> username: johnseller, password: Seller123!
                    </div>
                    <div class="demo-account">
                        <strong>Buyer:</strong> username: janebuyer, password: Buyer123!
                    </div>
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
