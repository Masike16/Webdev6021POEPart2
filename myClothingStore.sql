-- MySQL Database Export for Pastimes Clothing Store
-- Student Numbers: ST10451774 & ST10452404
-- Names: Acazia Ammon & Masike Jr Rasenyalo
-- Declaration: This code is our own work except where referenced
-- 
-- Complete database structure and sample data
-- Generated for Web Development POE assignment

-- Create database
CREATE DATABASE IF NOT EXISTS ClothingStore;
USE ClothingStore;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS tblOrderItems;
DROP TABLE IF EXISTS tblOrders;
DROP TABLE IF EXISTS tblMessages;
DROP TABLE IF EXISTS tblCart;
DROP TABLE IF EXISTS tblClothes;
DROP TABLE IF EXISTS tblUser;

-- Create tblUser table
CREATE TABLE tblUser (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tblClothes table
CREATE TABLE tblClothes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tblOrders table
CREATE TABLE tblOrders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (buyer_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    INDEX (buyer_id),
    INDEX (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tblOrderItems table
CREATE TABLE tblOrderItems (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    clothes_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES tblOrders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (clothes_id) REFERENCES tblClothes(clothes_id) ON DELETE CASCADE,
    INDEX (order_id),
    INDEX (clothes_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tblMessages table
CREATE TABLE tblMessages (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tblCart table (Database-based shopping cart)
CREATE TABLE tblCart (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample users
INSERT INTO tblUser (full_name, email, username, password_hash, role, is_verified, delivery_address) VALUES
('Admin User', 'admin@pastimes.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '123 Admin Street, Admin City, 0001'),
('John Seller', 'john@seller.com', 'johnseller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 1, '456 Seller Avenue, Seller Town, 0002'),
('Jane Buyer', 'jane@buyer.com', 'janebuyer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 1, '789 Buyer Road, Buyer City, 0003'),
('Mike Seller', 'mike@seller.com', 'mikeseller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 1, '321 Seller Lane, Seller Town, 0004'),
('Sarah Buyer', 'sarah@buyer.com', 'sarahbuyer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 1, '654 Buyer Drive, Buyer City, 0005');

-- Insert sample clothing products
INSERT INTO tblClothes (seller_id, product_name, description, price, brand, category, image_path, status) VALUES
(2, 'Vintage Nike Jacket', 'Classic 90s Nike windbreaker in excellent condition. Perfect for streetwear enthusiasts.', 89.99, 'Nike', 'Jackets', 'images/nike_jacket.jpg', 'available'),
(2, 'Levi\'s 501 Jeans', 'Authentic Levi\'s 501 vintage jeans. Great fit with minimal wear.', 65.00, 'Levi\'s', 'Pants', 'images/levis_jeans.jpg', 'available'),
(2, 'Adidas Originals Shirt', 'Retro Adidas polo shirt in mint condition. Classic three-stripes design.', 45.50, 'Adidas', 'Shirts', 'images/adidas_shirt.jpg', 'available'),
(4, 'Converse Chuck Taylors', 'Classic Converse high-top sneakers. Well-worn but plenty of life left.', 55.00, 'Converse', 'Shoes', 'images/converse_shoes.jpg', 'available'),
(4, 'Vintage Ray-Ban Sunglasses', 'Authentic Ray-Ban Wayfarer sunglasses from the 80s. Original case included.', 120.00, 'Ray-Ban', 'Accessories', 'images/rayban_accessories.jpg', 'available'),
(2, 'Tommy Hilfiger Polo', 'Classic Tommy Hilfiger polo shirt. Preppy style with iconic logo.', 38.99, 'Tommy Hilfiger', 'Shirts', 'images/tommy_shirt.jpg', 'available'),
(4, 'Doc Martens Boots', 'Original Doc Martens boots. Scuffed but durable and stylish.', 95.00, 'Dr. Martens', 'Shoes', 'images/docmartens_shoes.jpg', 'available'),
(2, 'Champion Hoodie', 'Vintage Champion hoodie. Comfortable and authentic streetwear.', 52.50, 'Champion', 'Shirts', 'images/champion_shirt.jpg', 'available');

-- Insert sample messages
INSERT INTO tblMessages (sender_id, receiver_id, message_text, is_read) VALUES
(3, 2, 'Hi! I\'m interested in your Nike Jacket. Is it still available?', 0),
(2, 3, 'Yes, it\'s still available! Would you like to see more photos?', 1),
(5, 4, 'Hello, I love your Converse shoes. What size are they?', 0),
(4, 5, 'They\'re size 9 US. Would you like to try them on?', 1);

-- Insert sample order
INSERT INTO tblOrders (buyer_id, total_amount, delivery_address, status) VALUES
(3, 155.49, '789 Buyer Road, Buyer City, 0003', 'delivered');

-- Insert sample order items
INSERT INTO tblOrderItems (order_id, clothes_id, quantity, price) VALUES
(1, 2, 1, 65.00),
(1, 3, 1, 45.50),
(1, 8, 1, 52.50);

-- Sample cart items (for testing)
INSERT INTO tblCart (user_id, clothes_id, quantity) VALUES
(5, 1, 1),
(5, 4, 1);

-- Create indexes for better performance
CREATE INDEX idx_clothes_status_category ON tblClothes(status, category);
CREATE INDEX idx_orders_buyer_date ON tblOrders(buyer_id, order_date);
CREATE INDEX idx_messages_receiver_unread ON tblMessages(receiver_id, is_read);
CREATE INDEX idx_cart_user_clothes ON tblCart(user_id, clothes_id);

-- Display completion message
SELECT 'Pastimes Clothing Store database created successfully!' as message,
       COUNT(*) as total_users FROM tblUser
UNION ALL
SELECT 'Sample products loaded:', COUNT(*) FROM tblClothes
UNION ALL
SELECT 'Sample orders created:', COUNT(*) FROM tblOrders
UNION ALL
SELECT 'Sample messages added:', COUNT(*) FROM tblMessages;
