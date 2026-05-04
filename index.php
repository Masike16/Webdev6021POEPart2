<?php
/**
 * Homepage for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Main landing page with featured products and hero section
 * Homepage layout inspired by Vinted (2023) clean design
 * Featured products grid influenced by Depop (2023) browsing experience
 */

session_start();
require_once 'DBConn.php';

$db = new DBConn();

// Get featured products (newest available items)
$featured_products = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT c.clothes_id, c.product_name, c.brand, c.category, c.price, c.image_path, c.created_at,
            u.full_name as seller_name
     FROM tblClothes c
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE c.status = 'available'
     ORDER BY c.created_at DESC
     LIMIT 8"
));

// Get categories for quick navigation
$categories = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT category, COUNT(*) as count
     FROM tblClothes 
     WHERE status = 'available'
     GROUP BY category
     ORDER BY count DESC"
));

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepareAndExecute("SELECT COUNT(*) as count FROM tblCart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
    $result = $db->getSingleRow($stmt);
    $cart_count = $result['count'];
}

// Display success/error messages from cart operations
$success_message = isset($_SESSION['cart_success']) ? $_SESSION['cart_success'] : '';
$error_message = isset($_SESSION['cart_error']) ? $_SESSION['cart_error'] : '';

// Clear session messages
unset($_SESSION['cart_success']);
unset($_SESSION['cart_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Second-Hand Fashion</title>
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
                <a href="index.php" class="active">Home</a>
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
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Discover Amazing Second-Hand Fashion</h1>
                <p>Find unique, pre-loved clothing items at great prices. Buy and sell with confidence on Pastimes.</p>
                <div class="hero-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="products.php" class="btn btn-primary btn-large">Browse Products</a>
                        <?php if ($_SESSION['role'] === 'seller'): ?>
                            <a href="add_product.php" class="btn btn-secondary btn-large">Sell Items</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="products.php" class="btn btn-primary btn-large">Start Shopping</a>
                        <a href="register.php" class="btn btn-secondary btn-large">Join Now</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-placeholder">👗👔👕</div>
            </div>
        </section>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Search Section -->
        <section class="search-section">
            <div class="search-container">
                <h2>Find Your Perfect Style</h2>
                <form action="products.php" method="GET" class="search-form">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search for brands, items, or styles..." class="search-input">
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories-section">
            <div class="section-header">
                <h2>Shop by Category</h2>
                <p>Browse our selection of second-hand fashion</p>
            </div>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?php echo urlencode($category['category']); ?>" class="category-card">
                        <div class="category-icon">
                            <?php
                            $icons = [
                                'Shirts' => '👔',
                                'Pants' => '👖',
                                'Jackets' => '🧥',
                                'Shoes' => '👟',
                                'Accessories' => '🎩'
                            ];
                            echo $icons[$category['category']] ?? '👕';
                            ?>
                        </div>
                        <h3><?php echo htmlspecialchars($category['category']); ?></h3>
                        <p><?php echo $category['count']; ?> items</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="featured-section">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Check out our latest additions</p>
            </div>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">👔</div>
                            <?php endif; ?>
                            <span class="badge badge-success">Available</span>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <p class="brand"><?php echo htmlspecialchars($product['brand']); ?></p>
                            <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                            <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                            <p class="seller">Sold by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="product_details.php?id=<?php echo $product['clothes_id']; ?>" 
                               class="btn btn-primary">View Details</a>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer'): ?>
                                <a href="add_to_cart.php?product_id=<?php echo $product['clothes_id']; ?>" 
                                   class="btn btn-success">Add to Cart</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-footer">
                <a href="products.php" class="btn btn-outline">View All Products</a>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="section-header">
                <h2>Why Choose Pastimes?</h2>
                <p>The best platform for second-hand fashion</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">♻️</div>
                    <h3>Sustainable Fashion</h3>
                    <p>Give pre-loved clothing a second life and reduce fashion waste</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Great Prices</h3>
                    <p>Find amazing deals on quality second-hand clothing items</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Secure Transactions</h3>
                    <p>Safe and secure buying and selling experience</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Direct Communication</h3>
                    <p>Message sellers directly to ask questions or negotiate</p>
                </div>
            </div>
        </section>

        <!-- How It Works -->
        <section class="how-it-works">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>Simple steps to buy and sell on Pastimes</p>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Sign Up</h3>
                    <p>Create your free account and get verified to start buying or selling</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Browse & Discover</h3>
                    <p>Search through our collection of quality second-hand fashion items</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Buy or Sell</h3>
                    <p>Purchase items you love or list your own pre-loved clothing</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Connect & Transact</h3>
                    <p>Communicate with buyers/sellers and complete secure transactions</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Pastimes</h3>
                    <p>Your trusted platform for second-hand fashion</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Categories</h4>
                    <?php foreach (array_slice($categories, 0, 4) as $category): ?>
                        <li><a href="products.php?category=<?php echo urlencode($category['category']); ?>"><?php echo htmlspecialchars($category['category']); ?></a></li>
                    <?php endforeach; ?>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>📧 support@pastimes.com</p>
                    <p>📞 012 345 6789</p>
                    <p>📍 South Africa</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Pastimes Second-Hand Fashion. All rights reserved.</p>
                <p>Student Project: ST10451774 & ST10452404</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
?>
