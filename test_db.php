<?php
/**
 * Database Test Script for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 */

echo "<h2>Database Connection Test</h2>";

// Test basic MySQLi connection
$host = "localhost";
$username = "root";
$password = "";
$database = "ClothingStore";

echo "<p>Testing connection to MySQL server...</p>";

try {
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>✗ MySQL connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✓ MySQL server connection successful</p>";
        
        // Test database creation
        $create_db = "CREATE DATABASE IF NOT EXISTS $database";
        if ($conn->query($create_db)) {
            echo "<p style='color: green;'>✓ Database '$database' created or already exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create database: " . $conn->error . "</p>";
        }
        
        // Select database
        $conn->select_db($database);
        echo "<p style='color: green;'>✓ Selected database '$database'</p>";
        
        // Test table creation
        $test_table = "CREATE TABLE IF NOT EXISTS test_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        if ($conn->query($test_table)) {
            echo "<p style='color: green;'>✓ Test table created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test table: " . $conn->error . "</p>";
        }
        
        // Clean up test table
        $conn->query("DROP TABLE IF EXISTS test_table");
        echo "<p>✓ Cleaned up test table</p>";
        
        $conn->close();
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
}

echo "<p><a href='loadClothingStore.php'>Run Full Database Setup</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>
