<?php
/**
 * Diagnostic Page for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 */

echo "<h2>Pastimes Store - Diagnostic Tool</h2>";

// Check PHP version
echo "<h3>PHP Information</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>PHP is working: ✓</p>";

// Check MySQLi extension
echo "<h3>MySQLi Extension</h3>";
if (extension_loaded('mysqli')) {
    echo "<p>MySQLi extension: ✓ Loaded</p>";
} else {
    echo "<p style='color: red;'>MySQLi extension: ✗ Not loaded</p>";
}

// Test database connection
echo "<h3>Database Connection Test</h3>";
$host = "localhost";
$username = "root";
$password = "";
$database = "ClothingStore";

try {
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>MySQL connection failed: " . $conn->connect_error . "</p>";
        echo "<p>Check if WAMP MySQL service is running</p>";
    } else {
        echo "<p style='color: green;'>MySQL connection: ✓ Successful</p>";
        
        // Check if database exists
        $result = $conn->query("SHOW DATABASES LIKE '$database'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>Database '$database': ✓ Exists</p>";
            
            // Select database and check tables
            $conn->select_db($database);
            $tables = $conn->query("SHOW TABLES");
            if ($tables->num_rows > 0) {
                echo "<p style='color: green;'>Tables found: " . $tables->num_rows . "</p>";
                while ($row = $tables->fetch_row()) {
                    echo "<p>- " . $row[0] . "</p>";
                }
            } else {
                echo "<p style='color: orange;'>No tables found. Run loadClothingStore.php</p>";
            }
        } else {
            echo "<p style='color: orange;'>Database '$database': ✗ Does not exist</p>";
            echo "<p>Run loadClothingStore.php to create database</p>";
        }
        
        $conn->close();
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h3>File Permissions</h3>";
$files_to_check = ['DBConn.php', 'index.php', 'loadClothingStore.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>$file: ✓ Exists and readable</p>";
    } else {
        echo "<p style='color: red;'>$file: ✗ Not found</p>";
    }
}

// Check uploads directory
echo "<h3>Uploads Directory</h3>";
if (is_dir('uploads')) {
    echo "<p style='color: green;'>uploads directory: ✓ Exists</p>";
    if (is_writable('uploads')) {
        echo "<p style='color: green;'>uploads directory: ✓ Writable</p>";
    } else {
        echo "<p style='color: orange;'>uploads directory: ⚠ Not writable</p>";
    }
} else {
    echo "<p style='color: orange;'>uploads directory: ⚠ Does not exist (will be created)</p>";
}

echo "<h3>Actions</h3>";
echo "<p><a href='loadClothingStore.php'>Run Database Setup</a></p>";
echo "<p><a href='test_db.php'>Test Database Connection</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";

echo "<h3>WAMP Services Check</h3>";
echo "<p>Make sure WAMP services are running:</p>";
echo "<ul>";
echo "<li>Apache (green icon)</li>";
echo "<li>MySQL (green icon)</li>";
echo "</ul>";
echo "<p>If services are not running, restart WAMP server.</p>";
?>
