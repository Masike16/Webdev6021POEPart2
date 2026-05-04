<?php
/**
 * Admin Dashboard for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Admin panel for user and product management
 * Following Depop (2023) admin dashboard patterns for user management
 * Product oversight inspired by Vinted (2023) admin tools
 */

session_start();
require_once 'DBConn.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit;
}

$db = new DBConn();
$conn = $db->getConnection();

// Handle user verification
if (isset($_GET['action']) && $_GET['action'] === 'verify_user' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $stmt = $db->prepareAndExecute("UPDATE tblUser SET is_verified = 1 WHERE user_id = ?", "i", [$user_id]);
    if ($stmt) {
        $success_message = "User verified successfully";
    }
}

// Handle user deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    $stmt = $db->prepareAndExecute("DELETE FROM tblUser WHERE user_id = ? AND role != 'admin'", "i", [$user_id]);
    if ($stmt) {
        $success_message = "User deleted successfully";
    }
}

// Handle product deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_product' && isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $stmt = $db->prepareAndExecute("DELETE FROM tblClothes WHERE clothes_id = ?", "i", [$product_id]);
    if ($stmt) {
        $success_message = "Product deleted successfully";
    }
}

// Get statistics
$stats = [
    'total_users' => 0,
    'verified_users' => 0,
    'pending_users' => 0,
    'total_products' => 0,
    'available_products' => 0,
    'sold_products' => 0
];

// User statistics
$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblUser");
$result = $db->getSingleRow($stmt);
$stats['total_users'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblUser WHERE is_verified = 1");
$result = $db->getSingleRow($stmt);
$stats['verified_users'] = $result['total'];

$stats['pending_users'] = $stats['total_users'] - $stats['verified_users'];

// Product statistics
$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes");
$result = $db->getSingleRow($stmt);
$stats['total_products'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes WHERE status = 'available'");
$result = $db->getSingleRow($stmt);
$stats['available_products'] = $result['total'];

$stmt = $db->prepareAndExecute("SELECT COUNT(*) as total FROM tblClothes WHERE status = 'sold'");
$result = $db->getSingleRow($stmt);
$stats['sold_products'] = $result['total'];

// Get recent users
$recent_users = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT user_id, full_name, email, username, role, is_verified, created_at 
     FROM tblUser 
     ORDER BY created_at DESC 
     LIMIT 10"
));

// Get pending users
$pending_users = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT user_id, full_name, email, username, role, created_at 
     FROM tblUser 
     WHERE is_verified = 0 
     ORDER BY created_at DESC"
));

// Get recent products
$recent_products = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT c.clothes_id, c.product_name, c.brand, c.category, c.price, c.status, c.created_at,
            u.full_name as seller_name, u.username as seller_username
     FROM tblClothes c
     JOIN tblUser u ON c.seller_id = u.user_id
     ORDER BY c.created_at DESC 
     LIMIT 10"
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes Second-Hand Fashion</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h1>Pastimes</h1>
                <span class="tagline">Admin Panel</span>
            </div>
            <div class="nav-links">
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="products.php">Products</a>
                <span class="user-info">Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <h1>Admin Dashboard</h1>
            <p>Manage users and products for Pastimes Second-Hand Fashion</p>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Verified Users</h3>
                    <div class="stat-number"><?php echo $stats['verified_users']; ?></div>
                </div>
                <div class="stat-card pending">
                    <h3>Pending Users</h3>
                    <div class="stat-number"><?php echo $stats['pending_users']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                </div>
                <div class="stat-card success">
                    <h3>Available Products</h3>
                    <div class="stat-number"><?php echo $stats['available_products']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Sold Products</h3>
                    <div class="stat-number"><?php echo $stats['sold_products']; ?></div>
                </div>
            </div>
            
            <!-- Pending Users Section -->
            <div class="admin-section">
                <h2>Pending User Verification</h2>
                <?php if (empty($pending_users)): ?>
                    <p>No pending users to verify.</p>
                <?php else: ?>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td class="actions">
                                            <a href="?action=verify_user&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-success btn-sm" 
                                               onclick="return confirm('Verify this user?')">Verify</a>
                                            <a href="?action=delete_user&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Delete this user?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Users Section -->
            <div class="admin-section">
                <h2>Recent Users</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td>
                                        <?php if ($user['is_verified']): ?>
                                            <span class="badge badge-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="actions">
                                        <?php if (!$user['is_verified'] && $user['role'] !== 'admin'): ?>
                                            <a href="?action=verify_user&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-success btn-sm">Verify</a>
                                        <?php endif; ?>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <a href="?action=delete_user&user_id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Delete this user?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Products Section -->
            <div class="admin-section">
                <h2>Recent Products</h2>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Seller</th>
                                <th>Status</th>
                                <th>Added Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td>R<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $product['status'] === 'available' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                    <td class="actions">
                                        <a href="product_details.php?id=<?php echo $product['clothes_id']; ?>" 
                                           class="btn btn-primary btn-sm">View</a>
                                        <a href="?action=delete_product&product_id=<?php echo $product['clothes_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
