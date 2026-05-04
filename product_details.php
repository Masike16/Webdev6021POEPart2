<?php
/**
 * Product Details Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Individual product view with seller information and messaging
 * Product detail layout inspired by Vinted (2023) product pages
 * Seller communication influenced by Depop (2023) messaging system
 */

session_start();
require_once 'DBConn.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: products.php');
    exit;
}

$db = new DBConn();

// Get product details
$product = $db->getSingleRow($db->prepareAndExecute(
    "SELECT c.clothes_id, c.product_name, c.brand, c.category, c.price, c.description, 
            c.image_path, c.status, c.created_at,
            u.user_id as seller_id, u.full_name as seller_name, u.username as seller_username
     FROM tblClothes c
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE c.clothes_id = ?",
    "i",
    [$product_id]
));

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get related products (same category, different product)
$related_products = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT c.clothes_id, c.product_name, c.brand, c.price, c.image_path,
            u.full_name as seller_name
     FROM tblClothes c
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE c.category = ? AND c.clothes_id != ? AND c.status = 'available'
     ORDER BY c.created_at DESC
     LIMIT 4",
    "si",
    [$product['category'], $product_id]
));

// Handle message sending
$message_sent = false;
$message_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    $message_text = trim($_POST['message_text']);
    
    if (empty($message_text)) {
        $message_error = 'Please enter a message';
    } elseif (strlen($message_text) > 1000) {
        $message_error = 'Message must be less than 1000 characters';
    } else {
        // Send message to seller
        $stmt = $db->prepareAndExecute(
            "INSERT INTO tblMessages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)",
            "iis",
            [$_SESSION['user_id'], $product['seller_id'], $message_text]
        );
        
        if ($stmt) {
            $message_sent = true;
        } else {
            $message_error = 'Failed to send message. Please try again.';
        }
    }
}

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM tblCart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
    $result = $db->getSingleRow($stmt);
    $cart_count = $result['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Pastimes Second-Hand Fashion</title>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="cart-link">
                        🛒 Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($_SESSION['role'] === 'seller'): ?>
                        <a href="seller_dashboard.php">Seller Dashboard</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="product-detail-container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Home</a> / 
                <a href="products.php">Products</a> / 
                <span><?php echo htmlspecialchars($product['product_name']); ?></span>
            </nav>

            <!-- Product Details -->
            <div class="product-detail">
                <div class="product-image-section">
                    <div class="main-image">
                        <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <?php else: ?>
                            <div class="placeholder-image large">👔</div>
                        <?php endif; ?>
                        <?php if ($product['status'] === 'available'): ?>
                            <span class="badge badge-success">Available</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Sold</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-info-section">
                    <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                    
                    <div class="product-meta">
                        <p class="brand"><?php echo htmlspecialchars($product['brand']); ?></p>
                        <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                    </div>

                    <div class="price-section">
                        <span class="price">R<?php echo number_format($product['price'], 2); ?></span>
                    </div>

                    <div class="description-section">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="seller-section">
                        <h3>Seller Information</h3>
                        <div class="seller-card">
                            <div class="seller-info">
                                <h4><?php echo htmlspecialchars($product['seller_name']); ?></h4>
                                <p>@<?php echo htmlspecialchars($product['seller_username']); ?></p>
                            </div>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']): ?>
                                <button class="btn btn-secondary" onclick="toggleMessageForm()">Message Seller</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($product['status'] === 'available' && isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
                        <div class="action-buttons">
                            <a href="add_to_cart.php?product_id=<?php echo $product['clothes_id']; ?>" 
                               class="btn btn-primary btn-large">Add to Cart</a>
                        </div>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <div class="action-buttons">
                            <a href="login.php" class="btn btn-primary btn-large">Login to Buy</a>
                        </div>
                    <?php endif; ?>

                    <div class="product-details">
                        <h3>Product Details</h3>
                        <ul>
                            <li><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></li>
                            <li><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></li>
                            <li><strong>Condition:</strong> Good</li>
                            <li><strong>Listed:</strong> <?php echo date('F d, Y', strtotime($product['created_at'])); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Message Form -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $product['seller_id']): ?>
                <div id="messageForm" class="message-form" style="display: none;">
                    <h3>Send Message to <?php echo htmlspecialchars($product['seller_name']); ?></h3>
                    
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success">
                            Message sent successfully!
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($message_error): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($message_error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="message_text">Your Message</label>
                            <textarea id="message_text" name="message_text" rows="4" 
                                      placeholder="Ask about the product, negotiate price, or arrange viewing..."
                                      maxlength="1000" required><?php echo isset($_POST['message_text']) ? htmlspecialchars($_POST['message_text']) : ''; ?></textarea>
                            <small>Maximum 1000 characters</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleMessageForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
                <div class="related-products">
                    <h3>Related Products</h3>
                    <div class="products-grid">
                        <?php foreach ($related_products as $related): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if ($related['image_path'] && file_exists($related['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($related['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">👔</div>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($related['product_name']); ?></h4>
                                    <p class="brand"><?php echo htmlspecialchars($related['brand']); ?></p>
                                    <p class="price">R<?php echo number_format($related['price'], 2); ?></p>
                                    <p class="seller"><?php echo htmlspecialchars($related['seller_name']); ?></p>
                                </div>
                                <div class="product-actions">
                                    <a href="product_details.php?id=<?php echo $related['clothes_id']; ?>" 
                                       class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
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

    <script>
        function toggleMessageForm() {
            const form = document.getElementById('messageForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            
            if (form.style.display === 'block') {
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
?>
