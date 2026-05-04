<?php
/**
 * User Profile Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * User profile with messaging system and order history
 * Profile layout inspired by Vinted (2023) user profiles
 * Messaging system influenced by Depop (2023) communication features
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = new DBConn();
$user_id = $_SESSION['user_id'];

// Handle message sending
$message_sent = false;
$message_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
    $message_text = trim($_POST['message_text']);
    
    if ($receiver_id === 0 || $receiver_id === $user_id) {
        $message_error = 'Invalid recipient';
    } elseif (empty($message_text)) {
        $message_error = 'Please enter a message';
    } elseif (strlen($message_text) > 1000) {
        $message_error = 'Message must be less than 1000 characters';
    } else {
        // Send message
        $stmt = $db->prepareAndExecute(
            "INSERT INTO tblMessages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)",
            "iis",
            [$user_id, $receiver_id, $message_text]
        );
        
        if ($stmt) {
            $message_sent = true;
        } else {
            $message_error = 'Failed to send message. Please try again.';
        }
    }
}

// Get user information
$user = $db->getSingleRow($db->prepareAndExecute(
    "SELECT user_id, full_name, email, username, role, is_verified, delivery_address, created_at
     FROM tblUser 
     WHERE user_id = ?",
    "i",
    [$user_id]
));

// Get user's messages (received and sent)
$received_messages = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT m.message_id, m.sender_id, m.message_text, m.is_read, m.created_at,
            u.full_name as sender_name, u.username as sender_username
     FROM tblMessages m
     JOIN tblUser u ON m.sender_id = u.user_id
     WHERE m.receiver_id = ?
     ORDER BY m.created_at DESC
     LIMIT 20",
    "i",
    [$user_id]
));

$sent_messages = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT m.message_id, m.receiver_id, m.message_text, m.created_at,
            u.full_name as receiver_name, u.username as receiver_username
     FROM tblMessages m
     JOIN tblUser u ON m.receiver_id = u.user_id
     WHERE m.sender_id = ?
     ORDER BY m.created_at DESC
     LIMIT 20",
    "i",
    [$user_id]
));

// Get order history for buyers
$order_history = [];
if ($user['role'] === 'buyer') {
    $order_history = $db->getMultipleRows($db->prepareAndExecute(
        "SELECT o.order_id, o.order_date, o.total_amount, o.status,
                COUNT(oi.order_item_id) as item_count
         FROM tblOrders o
         LEFT JOIN tblOrderItems oi ON o.order_id = oi.order_id
         WHERE o.buyer_id = ?
         GROUP BY o.order_id
         ORDER BY o.order_date DESC
         LIMIT 10",
        "i",
        [$user_id]
    ));
}

// Get seller statistics
$seller_stats = null;
if ($user['role'] === 'seller') {
    $stats = $db->getSingleRow($db->prepareAndExecute(
        "SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_products,
            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_products,
            SUM(CASE WHEN status = 'sold' THEN price ELSE 0 END) as total_revenue
         FROM tblClothes 
         WHERE seller_id = ?",
        "i",
        [$user_id]
    ));
    $seller_stats = $stats;
}

// Get list of users for messaging (excluding self)
$users_for_messaging = $db->getMultipleRows($db->prepareAndExecute(
    "SELECT user_id, full_name, username, role
     FROM tblUser 
     WHERE user_id != ? AND is_verified = 1
     ORDER BY full_name",
    "i",
    [$user_id]
));

// Mark messages as read
if (!empty($received_messages)) {
    $unread_ids = [];
    foreach ($received_messages as $msg) {
        if (!$msg['is_read']) {
            $unread_ids[] = $msg['message_id'];
        }
    }
    
    if (!empty($unread_ids)) {
        $placeholders = str_repeat('?,', count($unread_ids) - 1) . '?';
        $types = str_repeat('i', count($unread_ids));
        $stmt = $db->prepareAndExecute(
            "UPDATE tblMessages SET is_read = 1 WHERE message_id IN ($placeholders)",
            $types,
            $unread_ids
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Pastimes Second-Hand Fashion</title>
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
                <?php if ($_SESSION['role'] === 'buyer'): ?>
                    <a href="cart.php">🛒 Cart</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'seller'): ?>
                    <a href="seller_dashboard.php">Seller Dashboard</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>
                <span class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="profile-container">
            <header class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your account and view your activity</p>
            </header>

            <!-- User Information -->
            <div class="profile-section">
                <h2>Account Information</h2>
                <div class="user-info-card">
                    <div class="user-details">
                        <div class="detail-item">
                            <label>Name:</label>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Username:</label>
                            <span>@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Role:</label>
                            <span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="badge badge-<?php echo $user['is_verified'] ? 'success' : 'warning'; ?>">
                                <?php echo $user['is_verified'] ? 'Verified' : 'Pending Verification'; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Member Since:</label>
                            <span><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Delivery Address:</label>
                            <span><?php echo nl2br(htmlspecialchars($user['delivery_address'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seller Statistics -->
            <?php if ($seller_stats): ?>
                <div class="profile-section">
                    <h2>Seller Statistics</h2>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Products</h3>
                            <div class="stat-number"><?php echo $seller_stats['total_products']; ?></div>
                        </div>
                        <div class="stat-card success">
                            <h3>Available</h3>
                            <div class="stat-number"><?php echo $seller_stats['available_products']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Sold</h3>
                            <div class="stat-number"><?php echo $seller_stats['sold_products']; ?></div>
                        </div>
                        <div class="stat-card revenue">
                            <h3>Total Revenue</h3>
                            <div class="stat-number">R<?php echo number_format($seller_stats['total_revenue'], 2); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Order History (Buyers) -->
            <?php if ($user['role'] === 'buyer' && !empty($order_history)): ?>
                <div class="profile-section">
                    <h2>Order History</h2>
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_history as $order): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td><?php echo $order['item_count']; ?></td>
                                        <td>R<?php echo number_format($order['total_amount'], 2); ?></td>
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
                </div>
            <?php endif; ?>

            <!-- Messages -->
            <div class="profile-section">
                <h2>Messages</h2>
                
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
                
                <!-- Send Message Form -->
                <div class="message-form">
                    <h3>Send Message</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="receiver_id">Recipient:</label>
                            <select name="receiver_id" id="receiver_id" required>
                                <option value="">Select a user</option>
                                <?php foreach ($users_for_messaging as $msg_user): ?>
                                    <option value="<?php echo $msg_user['user_id']; ?>">
                                        <?php echo htmlspecialchars($msg_user['full_name']); ?> (@<?php echo htmlspecialchars($msg_user['username']); ?>) - <?php echo ucfirst($msg_user['role']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message_text">Message:</label>
                            <textarea id="message_text" name="message_text" rows="3" 
                                      placeholder="Type your message here..."
                                      maxlength="1000" required></textarea>
                            <small>Maximum 1000 characters</small>
                        </div>
                        
                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                
                <!-- Message Tabs -->
                <div class="message-tabs">
                    <button class="tab-btn active" onclick="showTab('received')">Received Messages</button>
                    <button class="tab-btn" onclick="showTab('sent')">Sent Messages</button>
                </div>
                
                <!-- Received Messages -->
                <div id="received-tab" class="message-list">
                    <?php if (empty($received_messages)): ?>
                        <p>No received messages.</p>
                    <?php else: ?>
                        <?php foreach ($received_messages as $msg): ?>
                            <div class="message-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?>">
                                <div class="message-header">
                                    <strong>From: <?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                    <span class="message-date"><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                </div>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="unread-badge">New</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Sent Messages -->
                <div id="sent-tab" class="message-list" style="display: none;">
                    <?php if (empty($sent_messages)): ?>
                        <p>No sent messages.</p>
                    <?php else: ?>
                        <?php foreach ($sent_messages as $msg): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <strong>To: <?php echo htmlspecialchars($msg['receiver_name']); ?></strong>
                                    <span class="message-date"><?php echo date('M d, Y g:i A', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.getElementById('received-tab').style.display = 'none';
            document.getElementById('sent-tab').style.display = 'none';
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
    <script src="js/script.js"></script>
</body>
</html>
?>
