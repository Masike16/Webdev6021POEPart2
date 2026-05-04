<?php
/**
 * Database Setup Script for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Creates all database tables with sample data
 * Following W3Schools (2021) database creation patterns
 * Inspired by Depop (2023) and Vinted (2023) e-commerce structures
 */

require_once 'DBConn.php';

$db = new DBConn();
$conn = $db->getConnection();

echo "<h2>Pastimes Clothing Store - Database Setup</h2>";

// Drop all tables if they exist
$tables = [
    'tblOrderItems',
    'tblOrders', 
    'tblMessages',
    'tblCart',
    'tblClothes',
    'tblUser'
];

foreach ($tables as $table) {
    $sql = "DROP TABLE IF EXISTS $table";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Dropped table: $table</p>";
    }
}

// Create tblUser table
$sql = "CREATE TABLE tblUser (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
    is_verified BOOLEAN DEFAULT FALSE,
    delivery_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (username),
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblUser</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblUser: " . $conn->error . "</p>";
}

// Create tblClothes table
$sql = "CREATE TABLE tblClothes (
    clothes_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    brand VARCHAR(100),
    category ENUM('Shirts', 'Pants', 'Jackets', 'Shoes', 'Accessories') NOT NULL,
    image_path VARCHAR(255),
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    INDEX (category),
    INDEX (status),
    INDEX (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblClothes</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblClothes: " . $conn->error . "</p>";
}

// Create tblOrders table
$sql = "CREATE TABLE tblOrders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (buyer_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    INDEX (buyer_id),
    INDEX (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblOrders</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblOrders: " . $conn->error . "</p>";
}

// Create tblOrderItems table
$sql = "CREATE TABLE tblOrderItems (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    clothes_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES tblOrders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE CASCADE,
    INDEX (order_id),
    INDEX (clothes_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblOrderItems</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblOrderItems: " . $conn->error . "</p>";
}

// Create tblMessages table
$sql = "CREATE TABLE tblMessages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    INDEX (sender_id),
    INDEX (receiver_id),
    INDEX (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblMessages</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblMessages: " . $conn->error . "</p>";
}

// Create tblCart table - Database-based cart system
$sql = "CREATE TABLE tblCart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    clothes_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_clothes (user_id, clothes_id),
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (clothes_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql)) {
    echo "<p style='color: green;'>✓ Created table: tblCart</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblCart: " . $conn->error . "</p>";
}

// Insert sample users
$users = [
    [
        'full_name' => 'Admin User',
        'email' => 'admin@pastimes.com',
        'username' => 'admin',
        'password_hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
        'role' => 'admin',
        'is_verified' => 1,
        'delivery_address' => '123 Admin Street, Admin City'
    ],
    [
        'full_name' => 'John Seller',
        'email' => 'john@seller.com',
        'username' => 'johnseller',
        'password_hash' => password_hash('Seller123!', PASSWORD_DEFAULT),
        'role' => 'seller',
        'is_verified' => 1,
        'delivery_address' => '456 Seller Avenue, Seller Town'
    ],
    [
        'full_name' => 'Jane Buyer',
        'email' => 'jane@buyer.com',
        'username' => 'janebuyer',
        'password_hash' => password_hash('Buyer123!', PASSWORD_DEFAULT),
        'role' => 'buyer',
        'is_verified' => 1,
        'delivery_address' => '789 Buyer Road, Buyer City'
    ],
    [
        'full_name' => 'Mike Seller',
        'email' => 'mike@seller.com',
        'username' => 'mikeseller',
        'password_hash' => password_hash('Seller123!', PASSWORD_DEFAULT),
        'role' => 'seller',
        'is_verified' => 1,
        'delivery_address' => '321 Seller Lane, Seller Town'
    ],
    [
        'full_name' => 'Sarah Buyer',
        'email' => 'sarah@buyer.com',
        'username' => 'sarahbuyer',
        'password_hash' => password_hash('Buyer123!', PASSWORD_DEFAULT),
        'role' => 'buyer',
        'is_verified' => 1,
        'delivery_address' => '654 Buyer Drive, Buyer City'
    ]
];

foreach ($users as $user) {
    $stmt = $db->prepareAndExecute(
        "INSERT INTO tblUser (full_name, email, username, password_hash, role, is_verified, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?)",
        "sssssis",
        [$user['full_name'], $user['email'], $user['username'], $user['password_hash'], $user['role'], $user['is_verified'], $user['delivery_address']]
    );
    
    if ($stmt) {
        echo "<p style='color: green;'>✓ Added user: " . $user['username'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding user: " . $user['username'] . "</p>";
    }
}

// Get seller IDs for sample products
$sellers = $db->getMultipleRows($db->prepareAndExecute("SELECT user_id FROM tblUser WHERE role = 'seller'"));

// Insert sample clothing products
$products = [
    [
        'product_name' => 'Vintage Nike Jacket',
        'description' => 'Classic 90s Nike windbreaker in excellent condition. Perfect for streetwear enthusiasts.',
        'price' => 89.99,
        'brand' => 'Nike',
        'category' => 'Jackets',
        'image_path' => 'images/nike_jacket.jpg'
    ],
    [
        'product_name' => 'Levi\'s 501 Jeans',
        'description' => 'Authentic Levi\'s 501 vintage jeans. Great fit with minimal wear.',
        'price' => 65.00,
        'brand' => 'Levi\'s',
        'category' => 'Pants',
        'image_path' => 'images/levis_jeans.jpg'
    ],
    [
        'product_name' => 'Adidas Originals Shirt',
        'description' => 'Retro Adidas polo shirt in mint condition. Classic three-stripes design.',
        'price' => 45.50,
        'brand' => 'Adidas',
        'category' => 'Shirts',
        'image_path' => 'images/adidas_shirt.jpg'
    ],
    [
        'product_name' => 'Converse Chuck Taylors',
        'description' => 'Classic Converse high-top sneakers. Well-worn but plenty of life left.',
        'price' => 55.00,
        'brand' => 'Converse',
        'category' => 'Shoes',
        'image_path' => 'images/converse_shoes.jpg'
    ],
    [
        'product_name' => 'Vintage Ray-Ban Sunglasses',
        'description' => 'Authentic Ray-Ban Wayfarer sunglasses from the 80s. Original case included.',
        'price' => 120.00,
        'brand' => 'Ray-Ban',
        'category' => 'Accessories',
        'image_path' => 'images/rayban_accessories.jpg'
    ],
    [
        'product_name' => 'Tommy Hilfiger Polo',
        'description' => 'Classic Tommy Hilfiger polo shirt. Preppy style with iconic logo.',
        'price' => 38.99,
        'brand' => 'Tommy Hilfiger',
        'category' => 'Shirts',
        'image_path' => 'images/tommy_shirt.jpg'
    ],
    [
        'product_name' => 'Doc Martens Boots',
        'description' => 'Original Doc Martens boots. Scuffed but durable and stylish.',
        'price' => 95.00,
        'brand' => 'Dr. Martens',
        'category' => 'Shoes',
        'image_path' => 'images/docmartens_shoes.jpg'
    ],
    [
        'product_name' => 'Champion Hoodie',
        'description' => 'Vintage Champion hoodie. Comfortable and authentic streetwear.',
        'price' => 52.50,
        'brand' => 'Champion',
        'category' => 'Shirts',
        'image_path' => 'images/champion_shirt.jpg'
    ]
];

foreach ($products as $index => $product) {
    $seller_id = $sellers[$index % count($sellers)]['user_id'];
    
    $stmt = $db->prepareAndExecute(
        "INSERT INTO tblClothes (seller_id, product_name, description, price, brand, category, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)",
        "issdsss",
        [$seller_id, $product['product_name'], $product['description'], $product['price'], $product['brand'], $product['category'], $product['image_path']]
    );
    
    if ($stmt) {
        echo "<p style='color: green;'>✓ Added product: " . $product['product_name'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding product: " . $product['product_name'] . "</p>";
    }
}

echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
echo "<p><strong>Admin Login:</strong> username: admin, password: Admin123!</p>";
echo "<p><strong>Seller Login:</strong> username: johnseller, password: Seller123!</p>";
echo "<p><strong>Buyer Login:</strong> username: janebuyer, password: Buyer123!</p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>
