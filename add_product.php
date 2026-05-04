<?php
/**
 * Add Product Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Product addition form for sellers
 * Following Depop (2023) product listing patterns
 * Image upload handling inspired by Vinted (2023) seller tools
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in and is a verified seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller' || !$_SESSION['is_verified']) {
    header('Location: login.php');
    exit;
}

$db = new DBConn();
$seller_id = $_SESSION['user_id'];

$errors = [];
$success = false;

// Process product addition form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate product name
    if (empty(trim($_POST['product_name']))) {
        $errors['product_name'] = 'Product name is required';
    } elseif (strlen(trim($_POST['product_name'])) < 3) {
        $errors['product_name'] = 'Product name must be at least 3 characters';
    }
    
    // Validate description
    if (empty(trim($_POST['description']))) {
        $errors['description'] = 'Description is required';
    } elseif (strlen(trim($_POST['description'])) < 10) {
        $errors['description'] = 'Description must be at least 10 characters';
    }
    
    // Validate price
    if (empty(trim($_POST['price']))) {
        $errors['price'] = 'Price is required';
    } elseif (!is_numeric($_POST['price']) || $_POST['price'] <= 0) {
        $errors['price'] = 'Price must be a positive number';
    } elseif ($_POST['price'] > 99999.99) {
        $errors['price'] = 'Price cannot exceed R99,999.99';
    }
    
    // Validate brand
    if (empty(trim($_POST['brand']))) {
        $errors['brand'] = 'Brand is required';
    }
    
    // Validate category
    if (empty($_POST['category']) || !in_array($_POST['category'], ['Shirts', 'Pants', 'Jackets', 'Shoes', 'Accessories'])) {
        $errors['category'] = 'Please select a valid category';
    }
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($file_type, $allowed_types)) {
            $errors['product_image'] = 'Only JPG, PNG, and GIF images are allowed';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors['product_image'] = 'Image size must be less than 5MB';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $upload_path = 'uploads/' . $filename;
            
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $errors['product_image'] = 'Failed to upload image';
            }
        }
    }
    
    // If no errors, add product
    if (empty($errors)) {
        $product_name = trim($_POST['product_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $brand = trim($_POST['brand']);
        $category = $_POST['category'];
        
        // Use default image if none uploaded
        if (empty($image_path)) {
            $image_path = 'images/placeholder.jpg';
        }
        
        // Insert product
        $stmt = $db->prepareAndExecute(
            "INSERT INTO tblClothes (seller_id, product_name, description, price, brand, category, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)",
            "issdsss",
            [$seller_id, $product_name, $description, $price, $brand, $category, $image_path]
        );
        
        if ($stmt) {
            $success = true;
            $_POST = []; // Clear form
        } else {
            $errors['general'] = 'Failed to add product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Pastimes Second-Hand Fashion</title>
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
                <a href="seller_dashboard.php">Seller Dashboard</a>
                <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="product-form-container">
            <header class="form-header">
                <h1>Add New Product</h1>
                <p>List your second-hand clothing item for sale</p>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h3>Product Added Successfully!</h3>
                    <p>Your product has been listed and is now available for buyers.</p>
                    <p><a href="seller_dashboard.php">View Your Products</a></p>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" class="product-form" enctype="multipart/form-data" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_name">Product Name *</label>
                            <input type="text" id="product_name" name="product_name" 
                                   value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>"
                                   class="<?php echo isset($errors['product_name']) ? 'error' : ''; ?>"
                                   required maxlength="150">
                            <?php if (isset($errors['product_name'])): ?>
                                <span class="error-message"><?php echo htmlspecialchars($errors['product_name']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="brand">Brand *</label>
                            <input type="text" id="brand" name="brand" 
                                   value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>"
                                   class="<?php echo isset($errors['brand']) ? 'error' : ''; ?>"
                                   required maxlength="100">
                            <?php if (isset($errors['brand'])): ?>
                                <span class="error-message"><?php echo htmlspecialchars($errors['brand']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category *</label>
                            <select id="category" name="category" 
                                    class="<?php echo isset($errors['category']) ? 'error' : ''; ?>" required>
                                <option value="">Select Category</option>
                                <option value="Shirts" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Shirts') ? 'selected' : ''; ?>>Shirts</option>
                                <option value="Pants" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Pants') ? 'selected' : ''; ?>>Pants</option>
                                <option value="Jackets" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Jackets') ? 'selected' : ''; ?>>Jackets</option>
                                <option value="Shoes" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Shoes') ? 'selected' : ''; ?>>Shoes</option>
                                <option value="Accessories" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <span class="error-message"><?php echo htmlspecialchars($errors['category']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="price">Price (R) *</label>
                            <input type="number" id="price" name="price" 
                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                                   class="<?php echo isset($errors['price']) ? 'error' : ''; ?>"
                                   required min="0.01" max="99999.99" step="0.01">
                            <?php if (isset($errors['price'])): ?>
                                <span class="error-message"><?php echo htmlspecialchars($errors['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="5" 
                                  class="<?php echo isset($errors['description']) ? 'error' : ''; ?>"
                                  required maxlength="1000"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['description']); ?></span>
                        <?php endif; ?>
                        <small>Describe the condition, size, material, and any other details (max 1000 characters)</small>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Product Image</label>
                        <input type="file" id="product_image" name="product_image" 
                               class="<?php echo isset($errors['product_image']) ? 'error' : ''; ?>"
                               accept="image/jpeg,image/jpg,image/png,image/gif">
                        <?php if (isset($errors['product_image'])): ?>
                            <span class="error-message"><?php echo htmlspecialchars($errors['product_image']); ?></span>
                        <?php endif; ?>
                        <small>Upload a clear photo of your item (JPG, PNG, GIF, max 5MB)</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">Add Product</button>
                        <a href="seller_dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
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
