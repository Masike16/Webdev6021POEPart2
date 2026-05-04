<?php
/**
 * Shopping Cart Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * 
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Only buyers can use cart
if ($_SESSION['role'] !== 'buyer') {
    header('Location: index.php');
    exit;
}

$db = new DBConn();
$user_id = $_SESSION['user_id'];

// Handle cart updates
$success_message = '';
$error_message = '';

// Remove item from cart
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['item_id'])) {
    $item_id = (int)$_GET['item_id'];
    
    // Verify item belongs to this user
    $stmt = $db->prepareAndExecute("SELECT cart_id FROM tblCart WHERE cart_id = ? AND user_id = ?", "ii", [$item_id, $user_id]);
    if ($db->getSingleRow($stmt)) {
        $stmt = $db->prepareAndExecute("DELETE FROM tblCart WHERE cart_id = ? AND user_id = ?", "ii", [$item_id, $user_id]);
        if ($stmt) {
            $success_message = "Item removed from cart";
        }
    }
}

// Update cart quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $cart_id => $quantity) {
            $cart_id = (int)$cart_id;
            $quantity = (int)$quantity;
            
            if ($quantity > 0 && $quantity <= 99) {
                // Verify item belongs to this user
                $stmt = $db->prepareAndExecute("SELECT cart_id FROM tblCart WHERE cart_id = ? AND user_id = ?", "ii", [$cart_id, $user_id]);
                if ($db->getSingleRow($stmt)) {
                    $stmt = $db->prepareAndExecute("UPDATE tblCart SET quantity = ? WHERE cart_id = ? AND user_id = ?", "iii", [$quantity, $cart_id, $user_id]);
                }
            }
        }
        $success_message = "Cart updated successfully";
    }
}

// Get cart items
$cart_items = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT cart.cart_id, cart.quantity,
            c.clothes_id, c.product_name, c.brand, c.price, c.image_path, c.status,
            u.full_name as seller_name
     FROM tblCart cart
     JOIN tblClothes c ON cart.clothes_id = c.clothes_id
     JOIN tblUser u ON c.seller_id = u.user_id
     WHERE cart.user_id = ? AND c.status = 'available'
     ORDER BY cart.added_at DESC",
    "i",
    [$user_id]
));

// Calculate totals
$subtotal = 0;
$total_items = 0;

foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $total_items += $item['quantity'];
}

$shipping = $subtotal > 0 ? 50.00 : 0; // Fixed shipping fee
$total = $subtotal + $shipping;

// Remove unavailable items from cart
if (!empty($cart_items)) {
    $unavailable_items = $db->getMultipleRows($db->prepareAndExecute(
        "SELECT cart.cart_id 
         FROM tblCart cart
         JOIN tblClothes c ON cart.clothes_id = c.clothes_id
         WHERE cart.user_id = ? AND c.status = 'sold'",
        "i",
        [$user_id]
    ));
    
    foreach ($unavailable_items as $item) {
        $db->prepareAndExecute("DELETE FROM tblCart WHERE cart_id = ?", "i", [$item['cart_id']]);
        $error_message = "Some items were removed because they are no longer available";
    }
}
?>
<!--Database-based shopping cart system with  the user isolation
  Cart functionality inspired by Vinted (2023) shopping experience
  User-specific cart management influenced by Depop (2023) cart system -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes Second-Hand Fashion</title>
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
                <a href="cart.php" class="active">🛒 Cart (<?php echo count($cart_items); ?>)</a>
                <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="cart-container">
            <header class="cart-header">
                <h1>Shopping Cart</h1>
                <p><?php echo count($cart_items); ?> items in your cart</p>
            </header>

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

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-state">
                        <h3>Your cart is empty</h3>
                        <p>Start shopping to add items to your cart!</p>
                        <a href="products.php" class="btn btn-primary btn-large">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <form method="POST" class="cart-form">
                            <div class="cart-items-list">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="cart-item">
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
                                            <p class="price">R<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                        
                                        <div class="item-quantity">
                                            <label for="qty_<?php echo $item['cart_id']; ?>">Quantity:</label>
                                            <input type="number" 
                                                   id="qty_<?php echo $item['cart_id']; ?>"
                                                   name="quantities[<?php echo $item['cart_id']; ?>]" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="99" 
                                                   class="quantity-input">
                                        </div>
                                        
                                        <div class="item-total">
                                            <p class="total-price">R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                        </div>
                                        
                                        <div class="item-actions">
                                            <a href="?action=remove&item_id=<?php echo $item['cart_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Remove this item from cart?')">Remove</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="cart-actions">
                                <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                                <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                            </div>
                        </form>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        
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
                        
                        <div class="checkout-actions">
                            <a href="checkout.php" class="btn btn-primary btn-large">Proceed to Checkout</a>
                            <p class="secure-checkout">
                                🔒 Secure checkout powered by Pastimes
                            </p>
                        </div>
                        
                        <div class="payment-methods">
                            <p>We accept:</p>
                            <div class="payment-icons">
                                <span>💳</span> <span>📱</span> <span>💰</span>
                            </div>
                        </div>
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

    <script src="js/script.js"></script>
</body>
</html>
?>
