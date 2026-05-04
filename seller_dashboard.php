<?php
/**
 * Seller Dashboard for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Seller panel for product management and inventory tracking
 * Following Depop (2023) seller dashboard patterns for inventory management
 * Product listing interface inspired by Vinted (2023) seller tools
 */

session_start();
require_once 'DBConn.php';

// Check if seller is logged in and verified
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller' || !$_SESSION['is_verified']) {
    header('Location: login.php');
    exit;
}

$db = new DBConn();
$conn = $db->getConnection();
$seller_id = $_SESSION['user_id'];

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    
    // Verify product belongs to this seller
    $stmt = $db->prepareAndExecute("SELECT clothes_id FROM tblClothes WHERE clothes_id = ? AND seller_id = ?", "ii", [$product_id, $seller_id]);
    if ($db->getSingleRow($stmt)) {
        $stmt = $db->prepareAndExecute("DELETE FROM tblClothes WHERE clothes_id = ? AND seller_id = ?", "ii", [$product_id, $seller_id]);
        if ($stmt) {
            $success_message = "Product deleted successfully";
        }
    }
}

// Handle marking product as sold
if (isset($_GET['action']) && $_GET['action'] === 'mark_sold' && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    
    // Verify product belongs to this seller
    $stmt = $db->prepareAndExecute("SELECT clothes_id FROM tblClothes WHERE clothes_id = ? AND seller_id = ?", "ii", [$product_id, $seller_id]);
    if ($db->getSingleRow($stmt)) {
        $stmt = $db->prepareAndExecute("UPDATE tblClothes SET status = 'sold' WHERE clothes_id = ? AND seller_id = ?", "ii", [$product_id, $seller_id]);
        if ($stmt) {
            $success_message = "Product marked as sold";
        }
    }
}

// Get seller statistics
$stats = [
    'total_products' => 0,
    'available_products' => 0,
    'sold_products' => 0,
    'total_value' => 0
];

// Product statistics
$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes WHERE seller_id = ?", "i", [$seller_id]);
$result = $db->getSingleRow($stmt);
$stats['total_products'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes WHERE seller_id = ? AND status = 'available'", "i", [$seller_id]);
$result = $db->getSingleRow($stmt);
$stats['available_products'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes WHERE seller_id = ? AND status = 'sold'", "i", [$seller_id]);
$result = $db->getSingleRow($stmt);
$stats['sold_products'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT SUM(price) as total FROM tblClothes WHERE seller_id = ? AND status = 'sold'", "i", [$seller_id]);
$result = $db->getSingleRow($stmt);
$stats['total_value'] = $result['total'] ?? 0;

// Get seller's products
$products = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT clothes_id, product_name, brand, category, price, status, image_path, created_at
     FROM tblClothes 
     WHERE seller_id = ?
     ORDER BY created_at DESC",
    "i",
    [$seller_id]
));

// Get recent orders for seller's products
$recent_orders = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT o.order_id, o.order_date, o.total_amount, o.status,
            oi.quantity, oi.price,
            c.product_name,
            u.full_name as buyer_name, u.email as buyer_email
     FROM tblOrders o
     JOIN tblOrderItems oi ON o.order_id = oi.order_id
     JOIN tblClothes c ON oi.clothes_id = c.clothes_id
     JOIN tblUser u ON o.buyer_id = u.user_id
     WHERE c.seller_id = ?
     ORDER BY o.order_date DESC
     LIMIT 10",
    "i",
    [$seller_id]
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Pastimes Second-Hand Fashion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1>Pastimes</h1>
                <span class="tagline">Seller Panel</span>
            </div>
            <div class="nav-links">
                <a href="seller_dashboard.php">Dashboard</a>
                <a href="add_product.php">Add Product</a>
                <a href="products.php">Browse Products</a>
                <span class="user-info">Seller: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <h1>Seller Dashboard</h1>
            <p>Manage your clothing inventory and track sales</p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                </div>
                <div class="stat-card success">
                    <h3>Available</h3>
                    <div class="stat-number"><?php echo $stats['available_products']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Sold</h3>
                    <div class="stat-number"><?php echo $stats['sold_products']; ?></div>
                </div>
                <div class="stat-card revenue">
                    <h3>Total Revenue</h3>
                    <div class="stat-number">R<?php echo number_format($stats['total_value'], 2); ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="add_product.php" class="btn btn-primary btn-large">➕ Add New Product</a>
                <a href="products.php" class="btn btn-secondary">🛍️ Browse All Products</a>
                <a href="profile.php" class="btn btn-secondary">👤 View Profile</a>
            </div>
            
            <!-- My Products Section -->
            <div class="seller-section">
                <h2>My Products</h2>
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <h3>No products listed yet</h3>
                        <p>Start selling by adding your first product.</p>
                        <a href="add_product.php" class="btn btn-primary">Add Your First Product</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if ($product['image_path'] && file_exists($product['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">👔</div>
                                    <?php endif; ?>
                                    <span class="status-badge badge-<?php echo $product['status'] === 'available' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                    <p class="brand"><?php echo htmlspecialchars($product['brand']); ?></p>
                                    <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                                    <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="date">Added: <?php echo date('M d, Y', strtotime($product['created_at'])); ?></p>
                                </div>
                                <div class="product-actions">
                                    <a href="edit_product.php?id=<?php echo $product['clothes_id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <?php if ($product['status'] === 'available'): ?>
                                        <a href="?action=mark_sold&product_id=<?php echo $product['clothes_id']; ?>" 
                                           class="btn btn-warning btn-sm"
                                           onclick="return confirm('Mark this product as sold?')">Mark Sold</a>
                                    <?php endif; ?>
                                    <a href="?action=delete_product&product_id=<?php echo $product['clothes_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Delete this product?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Orders Section -->
            <div class="seller-section">
                <h2>Recent Orders</h2>
                <?php if (empty($recent_orders)): ?>
                    <p>No orders received yet.</p>
                <?php else: ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product</th>
                                    <th>Buyer</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['buyer_name']); ?><br>
                                            <small><?php echo htmlspecialchars($order['buyer_email']); ?></small>
                                        </td>
                                        <td><?php echo $order['quantity']; ?></td>
                                        <td>R<?php echo number_format($order['price'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
