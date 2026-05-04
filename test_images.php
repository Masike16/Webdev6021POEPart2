<?php
/**
 * Test Images Script for Pastimes Clothing Store
 * Verifies image display functionality
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 */

require_once 'DBConn.php';

$db = new DBConn();

echo "<h2>Image Display Test</h2>";

// Get all products with their image paths
$products = $db->getMultipleRows($db->prepareAndExecute("SELECT clothes_id, product_name, image_path FROM tblClothes"));

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; padding: 20px;'>";

foreach ($products as $product) {
    $image_path = $product['image_path'];
    $product_name = $product['product_name'];
    $product_id = $product['clothes_id'];
    
    echo "<div style='border: 1px solid #ddd; padding: 15px; border-radius: 8px;'>";
    echo "<h3>" . htmlspecialchars($product_name) . "</h3>";
    echo "<p><strong>Path:</strong> " . htmlspecialchars($image_path) . "</p>";
    
    if ($image_path && file_exists($image_path)) {
        echo "<img src='" . htmlspecialchars($image_path) . "' alt='" . htmlspecialchars($product_name) . "' 
              style='max-width: 100%; height: 200px; object-fit: cover; border: 1px solid #ccc;'>";
        echo "<p style='color: green;'>✓ Image loads successfully</p>";
    } else {
        echo "<div style='width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px solid #ccc;'>";
        echo "<span style='color: #666;'>Image not found</span>";
        echo "</div>";
        echo "<p style='color: red;'>✗ Image file not found</p>";
    }
    
    echo "</div>";
}

echo "</div>";

echo "<div style='padding: 20px;'>";
echo "<h3>Directory Structure Check:</h3>";

// Check images directory structure
$images_dir = 'images';
if (is_dir($images_dir)) {
    echo "<p style='color: green;'>✓ Images directory exists</p>";
    
    $categories = ['shirts', 'pants', 'jackets', 'shoes', 'accessories'];
    foreach ($categories as $category) {
        $category_dir = $images_dir . '/' . $category;
        if (is_dir($category_dir)) {
            $files = glob($category_dir . '/*.{jpg,jpeg,png,gif,svg}', GLOB_BRACE);
            echo "<p style='color: green;'>✓ $category directory: " . count($files) . " files</p>";
        } else {
            echo "<p style='color: red;'>✗ $category directory missing</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Images directory missing</p>";
}

// Check uploads directory
if (is_dir('uploads')) {
    echo "<p style='color: green;'>✓ Uploads directory exists</p>";
} else {
    echo "<p style='color: orange;'>⚠ Uploads directory missing (will be created when needed)</p>";
}

echo "</div>";

echo "<p><a href='products.php'>View Products Page</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>
