<?php
/**
 * User Table Creation Script for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This  code is our own work except where referenced
 * 
 * Creates and loads the sample tblUser table from userData.txt
 * Following the W3Schools (2021) database creation patterns
 */

require_once 'DBConn.php';

$db = new DBConn();
$conn = $db->getConnection();

echo "<h2>User Table Setup</h2>";

// Check if tblUser exists and delete it
$checkTable = $conn->query("SHOW TABLES LIKE 'tblUser'");
if ($checkTable->num_rows > 0) {
    $dropTable = "DROP TABLE tblUser";
    if ($conn->query($dropTable)) {
        echo "<p style='color: green;'>✓ Dropped existing tblUser table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error dropping tblUser table: " . $conn->error . "</p>";
        exit;
    }
}

// Create tblUser table
// Table structure inspired by W3Schools (2021) user management examples
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
    echo "<p style='color: green;'>✓ Created tblUser table</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating tblUser table: " . $conn->error . "</p>";
    exit;
}

// Load data from userData.txt
$dataFile = 'userData.txt';
if (file_exists($dataFile)) {
    $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $loadedCount = 0;
    
    foreach ($lines as $line) {
        $parts = explode(',', $line);
        if (count($parts) === 4) {
            $full_name = trim($parts[0]);
            $email = trim($parts[1]);
            $password_hash = trim($parts[2]);
            $username = trim($parts[3]);
            
            // Generate username from email if not provided
            if (empty($username)) {
                $username = explode('@', $email)[0];
            }
            
            // Insert user
            $stmt = $db->prepareAndExecute(
                "INSERT INTO tblUser (full_name, email, username, password_hash, role, is_verified, delivery_address) VALUES (?, ?, ?, ?, 'buyer', 0, 'Default Address')",
                "ssss",
                [$full_name, $email, $username, $password_hash]
            );
            
            if ($stmt) {
                $loadedCount++;
                echo "<p style='color: green;'>✓ Loaded user: " . htmlspecialchars($username) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Error loading user: " . htmlspecialchars($username) . "</p>";
            }
        }
    }
    
    echo "<h3>Loaded $loadedCount users from userData.txt</h3>";
} else {
    echo "<p style='color: orange;'>⚠ userData.txt file not found. No sample data loaded.</p>";
}

echo "<p><a href='index.php'>Go to Homepage</a></p>";
echo "<p><a href='loadClothingStore.php'>Setup Complete Database</a></p>";
?>
