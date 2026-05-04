<?php
/**
 * Checkout Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Order processing and checkout flow
 * Checkout system inspired by Vinted (2023) purchase process
 * Order management influenced by Depop (2023) transaction system
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}

$db = new DBConn();
$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT cart.cart_id, cart.quantity,
            c.clothes_id, c.product_name, c.brand, c.price, c.image_path, c.status,
            u.full_name as seller_name, u.user_id as seller_id
     FROM tblCart cart
     JOIN tblClothes c ON cart.clothes_id = c.clothes_id
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE cart.user_id = ? AND c.status = 'available'
     ORDER BY cart.added_at DESC",
    "i",
    [$user_id]
));

// Check if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

$shipping = 50.00; // Fixed shipping fee
$total = $subtotal + $shipping;

// Process checkout
$order_placed = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Validate delivery address
    $delivery_address = trim($_POST['delivery_address']);
    
    if (empty($delivery_address)) {
        $error_message = 'Delivery address is required';
    } elseif (strlen($delivery_address) < 10) {
        $error_message = 'Please provide a complete delivery address';
    } else {
        // Start transaction
        $conn = $db->getConnection();
        $conn->begin_transaction();
        
        try {
            // Create order
            $stmt = $db->prepareAndExecute(
                "INSERT INTO tblOrders (buyer_id, total_amount, delivery_address, status) VALUES (?, ?, ?, ?)",
                "idss",
                [$user_id, $total, $delivery_address, 'pending']
            );
            
            if (!$stmt) {
                throw new Exception('Failed to create order');
            }
            
            $order_id = $db->getLastInsertId();
            
            // Add order items and mark products as sold
            foreach ($cart_items as $item) {
                // Add order item
                $stmt = $db->prepareAndExecute(
                    "INSERT INTO tblOrderItems (order_id, clothes_id, quantity, price) VALUES (?, ?, ?, ?)",
                    "iiid",
                    [$order_id, $item['clothes_id'], $item['quantity'], $item['price']]
                );
                
                if (!$stmt) {
                    throw new Exception('Failed to add order item');
                }
                
                // Mark product as sold
                $stmt = $db->prepareAndExecute(
                    "UPDATE tblClothes SET status = 'sold' WHERE clothes_id = ?",
                    "i",
                    [$item['clothes_id']]
                );
                
                if (!$stmt) {
                    throw new Exception('Failed to update product status');
                }
            }
            
            // Clear cart
            $stmt = $db->prepareAndExecute("DELETE FROM tblCart WHERE user_id = ?", "i", [$user_id]);
            
            if (!$stmt) {
                throw new Exception('Failed to clear cart');
            }
            
            // Commit transaction
            $conn->commit();
            $order_placed = true;
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=$order_id");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction
            $conn->rollback();
            $error_message = 'Order failed. Please try again. Error: ' . $e->getMessage();
        }
    }
}

// Get user's delivery address
$user_info = $db->getSingleRow($db->prepareAndExecute(
    "SELECT delivery_address FROM tblUser WHERE user_id = ?",
    "i",
    [$user_id]
));

$default_address = $user_info ? $user_info['delivery_address'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastimes Second-Hand Fashion</title>
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
                <a href="cart.php">🛒 Cart</a>
                <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="checkout-container">
            <header class="checkout-header">
                <h1>Checkout</h1>
                <p>Complete your purchase</p>
            </header>

            <!-- Progress Steps -->
            <div class="checkout-progress">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Cart</span>
                </div>
                <div class="step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Checkout</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Confirmation</span>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="checkout-content">
                <!-- Order Items -->
                <div class="order-section">
                    <h2>Order Items</h2>
                    <div class="order-items-list">
                        <?php foreach ($cart_items as $item): ?>
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
                            <span>Subtotal (<?php echo $total_items; ?> items):</span>
                            <span>R<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>R<?php echo number_format($shipping, 2); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>R<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="delivery-section">
                    <h2>Delivery Information</h2>
                    <form method="POST" class="checkout-form">
                        <div class="form-group">
                            <label for="delivery_address">Delivery Address *</label>
                            <textarea id="delivery_address" name="delivery_address" rows="4" 
                                      placeholder="Enter your complete delivery address including street, city, and postal code"
                                      required><?php echo isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : htmlspecialchars($default_address); ?></textarea>
                            <small>Please provide a complete address for delivery</small>
                        </div>

                        <!-- Payment Information -->
                        <div class="payment-section">
                            <h2>Payment Method</h2>
                            <div class="payment-options">
                                <div class="payment-option selected">
                                    <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                    <label for="cod">
                                        <span class="payment-icon">💰</span>
                                        <div class="payment-details">
                                            <strong>Cash on Delivery</strong>
                                            <p>Pay when you receive your items</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" id="card" name="payment_method" value="card">
                                    <label for="card">
                                        <span class="payment-icon">💳</span>
                                        <div class="payment-details">
                                            <strong>Credit/Debit Card</strong>
                                            <p>Secure online payment (Coming Soon)</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="terms-section">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="terms" required>
                                    <span>I agree to the <a href="#" class="link">Terms and Conditions</a> and understand that this is a final purchase</span>
                                </label>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <div class="checkout-actions">
                            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                            <button type="submit" name="place_order" class="btn btn-primary btn-large">Place Order</button>
                        </div>

                        <!-- Security Notice -->
                        <div class="security-notice">
                            <p>🔒 Your order information is secure and protected</p>
                            <p>By placing this order, you agree to our terms of service</p>
                        </div>
                    </form>
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
