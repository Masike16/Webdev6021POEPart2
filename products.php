<?php
/**
 * Products Browsing Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Product catalog with search and filtering
 * Grid layout inspired by Vinted (2023) product browsing interface
 * Search functionality influenced by Depop (2023) filtering system
 */

session_start();
require_once 'DBConn.php';

$db = new DBConn();

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query
$where_conditions = ["c.status = 'available'"];
$params = [];
$types = "";

// Add search condition
if (!empty($search)) {
    $where_conditions[] = "(c.product_name LIKE ? OR c.brand LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

// Add category filter
if (!empty($category)) {
    $where_conditions[] = "c.category = ?";
    $params[] = $category;
    $types .= "s";
}

// Add brand filter
if (!empty($brand)) {
    $where_conditions[] = "c.brand LIKE ?";
    $params[] = "%$brand%";
    $types .= "s";
}

// Add sorting
$order_clause = "ORDER BY ";
switch ($sort) {
    case 'price_low':
        $order_clause .= "c.price ASC";
        break;
    case 'price_high':
        $order_clause .= "c.price DESC";
        break;
    case 'name':
        $order_clause .= "c.product_name ASC";
        break;
    case 'newest':
    default:
        $order_clause .= "c.created_at DESC";
        break;
}

// Construct final query
$where_clause = "WHERE " . implode(" AND ", $where_conditions);
$sql = "SELECT c.clothes_id, c.product_name, c.brand, c.category, c.price, c.image_path, c.created_at,
               u.full_name as seller_name, u.username as seller_username
        FROM tblClothes c
        JOIN tblUser u ON c.seller_id = u.user_id
        $where_clause
        $order_clause";

// Execute query
if (!empty($params)) {
    $stmt = $db->prepareAndExecute($sql, $types, $params);
} else {
    $stmt = $db->prepareAndExecute($sql);
}

$products = $db->getMultipleRows($stmt);

// Get unique categories and brands for filters
$categories = $db->getMultipleRows($db->prepareAndExecute("SELECT DISTINCT category FROM tblClothes WHERE status = 'available' ORDER BY category"));
$brands = $db->getMultipleRows($db->prepareAndExecute("SELECT DISTINCT brand FROM tblClothes WHERE status = 'available' ORDER BY brand"));

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
    <title>Products - Pastimes Second-Hand Fashion</title>
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
                <a href="products.php" class="active">Products</a>
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
        <div class="products-container">
            <header class="products-header">
                <h1>Browse Products</h1>
                <p>Discover amazing second-hand fashion items</p>
            </header>

            <!-- Search and Filters -->
            <div class="search-filters">
                <form method="GET" class="search-form">
                    <div class="search-bar">
                        <input type="text" name="search" placeholder="Search products, brands..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input">
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                    </div>
                    
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="category">Category</label>
                            <select name="category" id="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                            <?php echo ($category === $cat['category']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="brand">Brand</label>
                            <select name="brand" id="brand">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $br): ?>
                                    <option value="<?php echo htmlspecialchars($br['brand']); ?>" 
                                            <?php echo ($brand === $br['brand']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($br['brand']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort">Sort By</label>
                            <select name="sort" id="sort">
                                <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo ($sort === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo ($sort === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name: A-Z</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-secondary">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline">Clear All</a>
                    </div>
                </form>
            </div>

            <!-- Results Count -->
            <div class="results-info">
                <p>Found <?php echo count($products); ?> products</p>
                <?php if (!empty($search) || !empty($category) || !empty($brand)): ?>
                    <a href="products.php" class="btn btn-sm btn-outline">Clear Filters</a>
                <?php endif; ?>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <h3>No products found</h3>
                        <p>Try adjusting your search or filters to find what you're looking for.</p>
                        <a href="products.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
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
