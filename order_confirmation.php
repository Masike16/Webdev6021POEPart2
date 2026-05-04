<?php
/**
 * Order Confirmation Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Order receipt and confirmation display
 * Order confirmation inspired by Vinted (2023) purchase confirmation
 * Receipt layout influenced by Depop (2023) order summary
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id === 0) {
    header('Location: index.php');
    exit;
}

$db = new DBConn();
$user_id = $_SESSION['user_id'];

// Get order details
$order = $db->getSingleRow($db->prepareAndExecute(
    "SELECT order_id, order_date, total_amount, delivery_address, status
     FROM tblOrders 
     WHERE order_id = ? AND buyer_id = ?",
    "ii",
    [$order_id, $user_id]
));

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$order_items = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT oi.quantity, oi.price,
            c.product_name, c.brand, c.image_path,
            u.full_name as seller_name
     FROM tblOrderItems oi
     JOIN tblClothes c ON oi.clothes_id = c.clothes_id
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE oi.order_id = ?",
    "i",
    [$order_id]
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Pastimes Second-Hand Fashion</title>
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
                <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="confirmation-container">
            <header class="confirmation-header">
                <div class="success-icon">✅</div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for your purchase. Your order has been successfully placed.</p>
            </header>

            <!-- Order Details -->
            <div class="order-details">
                <div class="order-info">
                    <h2>Order Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Order Number:</label>
                            <span>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Order Date:</label>
                            <span><?php echo date('F d, Y \a\t g:i A', strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Order Status:</label>
                            <span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Payment Method:</label>
                            <span>Cash on Delivery</span>
                        </div>
                    </div>
                </div>

                <div class="delivery-info">
                    <h2>Delivery Address</h2>
                    <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items-section">
                <h2>Order Items</h2>
                <div class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php if ($item['image_path'] && file_exists($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">👔</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <p class="brand"><?php echo htmlspecialchars($item['brand']); ?></p>
                                <p class="seller">Sold by <?php echo htmlspecialchars($item['seller_name']); ?></p>
                            </div>
                            
                            <div class="item-quantity">
                                <span>Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            
                            <div class="item-price">
                                <span>R<?php echo number_format($item['price'], 2); ?></span>
                                <span class="item-total">R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>R<?php echo number_format($order['total_amount'] - 50, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>R50.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Paid:</span>
                        <span>R<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h2>What's Next?</h2>
                <div class="steps-list">
                    <div class="step">
                        <div class="step-icon">📦</div>
                        <div class="step-content">
                            <h3>Order Processing</h3>
                            <p>Your order is being processed by our team</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">🚚</div>
                        <div class="step-content">
                            <h3>Delivery</h3>
                            <p>Your items will be delivered to your address within 3-5 business days</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">💰</div>
                        <div class="step-content">
                            <h3>Payment</h3>
                            <p>Pay the delivery person when you receive your items</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="confirmation-actions">
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                <a href="index.php" class="btn btn-secondary">Return to Home</a>
                <button onclick="window.print()" class="btn btn-outline">Print Receipt</button>
            </div>

            <!-- Support -->
            <div class="support-info">
                <h3>Need Help?</h3>
                <p>If you have any questions about your order, please contact our support team:</p>
                <p>📧 Email: support@pastimes.com</p>
                <p>📞 Phone: 012 345 6789</p>
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
