<?php
/**
 * User Registration Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * User registration with validation and password hashing
 * Following PHP.net (2022) security best practices
 * Registration flow inspired by Depop (2023) user onboarding
 */

session_start();
require_once 'DBConn.php';

$errors = [];
$success = false;

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate full name
    if (empty(trim($_POST['full_name']))) {
        $errors['full_name'] = 'Full name is required';
    } elseif (strlen(trim($_POST['full_name'])) < 2) {
        $errors['full_name'] = 'Full name must be at least 2 characters';
    }
    
    // Validate email
    if (empty(trim($_POST['email']))) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    // Validate username
    if (empty(trim($_POST['username']))) {
        $errors['username'] = 'Username is required';
    } elseif (strlen(trim($_POST['username'])) < 3) {
        $errors['username'] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST['username']))) {
        $errors['username'] = 'Username can only contain letters, numbers, and underscores';
    }
    
    // Validate password
    if (empty(trim($_POST['password']))) {
        $errors['password'] = 'Password is required';
    } elseif (strlen(trim($_POST['password'])) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    }
    
    // Validate password confirmation
    if (empty(trim($_POST['password_confirm']))) {
        $errors['password_confirm'] = 'Please confirm your password';
    } elseif (trim($_POST['password']) !== trim($_POST['password_confirm'])) {
        $errors['password_confirm'] = 'Passwords do not match';
    }
    
    // Validate role
    if (empty($_POST['role']) || !in_array($_POST['role'], ['buyer', 'seller'])) {
        $errors['role'] = 'Please select a valid role';
    }
    
    // Validate delivery address
    if (empty(trim($_POST['delivery_address']))) {
        $errors['delivery_address'] = 'Delivery address is required';
    }
    
    // If no validation errors, proceed with registration
    if (empty($errors)) {
        $db = new DBConn();
        $conn = $db->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepareAndExecute("SELECT user_id FROM tblUser WHERE email = ?", "s", [trim($_POST['email'])]);
        if ($db->getSingleRow($stmt)) {
            $errors['email'] = 'Email is already registered';
        }
        
        // Check if username already exists
        $stmt = $db->prepareAndExecute("SELECT user_id FROM tblUser WHERE username = ?", "s", [trim($_POST['username'])]);
        if ($db->getSingleRow($stmt)) {
            $errors['username'] = 'Username is already taken';
        }
        
        // If still no errors, create user
        if (empty($errors)) {
            $full_name = trim($_POST['full_name']);
            $email = trim($_POST['email']);
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $role = $_POST['role'];
            $delivery_address = trim($_POST['delivery_address']);
            
            // Hash password using secure algorithm
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $db->prepareAndExecute(
                "INSERT INTO tblUser (full_name, email, username, password_hash, role, delivery_address) VALUES (?, ?, ?, ?, ?, ?)",
                "ssssss",
                [$full_name, $email, $username, $password_hash, $role, $delivery_address]
            );
            
            if ($stmt) {
                $success = true;
                // Clear form data
                $_POST = [];
            } else {
                $errors['general'] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Pastimes Second-Hand Fashion</title>
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
                <a href="login.php">Login</a>
                <a href="admin_login.php">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="auth-container">
            <div class="auth-card">
                <h2>Create Account</h2>
                <p>Join Pastimes and start buying or selling second-hand fashion</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h3>Registration Successful!</h3>
                        <p>Your account has been created. Please wait for admin verification before you can log in.</p>
                        <p><a href="login.php">Go to Login</a></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                <form method="POST" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                               class="<?php echo isset($errors['full_name']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['full_name'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['full_name']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               class="<?php echo isset($errors['username']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['username']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" 
                               class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                               required minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['password']); ?></span>
                        <?php endif; ?>
                        <small>Must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" 
                               class="<?php echo isset($errors['password_confirm']) ? 'error' : ''; ?>"
                               required>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['password_confirm']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Account Type *</label>
                        <select id="role" name="role" class="<?php echo isset($errors['role']) ? 'error' : ''; ?>" required>
                            <option value="">Select Account Type</option>
                            <option value="buyer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                            <option value="seller" <?php echo (isset($_POST['role']) && $_POST['role'] === 'seller') ? 'selected' : ''; ?>>Seller</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['role']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="delivery_address">Delivery Address *</label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" 
                                  class="<?php echo isset($errors['delivery_address']) ? 'error' : ''; ?>"
                                  required><?php echo isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : ''; ?></textarea>
                        <?php if (isset($errors['delivery_address'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['delivery_address']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large">Create Account</button>
                </form>
                
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p>Are you an administrator? <a href="admin_login.php">Admin Login</a></p>
                </div>
                <?php endif; ?>
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
