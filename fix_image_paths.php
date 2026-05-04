<?php
/**
 * Fix Image Paths Script for Pastimes Clothing Store
 * Updates database image paths to match actual file locations
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 */

require_once 'DBConn.php';

$db = new DBConn();
$conn = $db->getConnection();

echo "<h2>Fixing Image Paths</h2>";

// Define the correct image mappings
$image_mappings = [
    'images/nike_jacket.jpg' => 'images/jackets/Vintage_Nike_Jacket.jpg',
    'images/levis_jeans.jpg' => 'images/pants/levis_501_jeans.jpg',
    'images/adidas_shirt.jpg' => 'images/shirts/Adidas_Originals_Shirt.jpg',
    'images/converse_shoes.jpg' => 'images/shoes/Converse_Chuck_Taylors.jpg',
    'images/rayban_accessories.jpg' => 'images/accessories/Vintage_Ray-Ban_Sunglasses.jpg',
    'images/tommy_shirt.jpg' => 'images/shirts/Tommy_Hilfiger_Polo.jpg',
    'images/docmartens_shoes.jpg' => 'images/shoes/Doc_Martens_Boots.jpg',
    'images/champion_shirt.jpg' => 'images/shirts/Champion_Hoodie.jpg'
];

// Update each image path
foreach ($image_mappings as $old_path => $new_path) {
    $stmt = $db->prepareAndExecute(
        "UPDATE tblClothes SET image_path = ? WHERE image_path = ?",
        "ss",
        [$new_path, $old_path]
    );
    
    if ($stmt) {
        $affected_rows = $conn->affected_rows;
        if ($affected_rows > 0) {
            echo "<p style='color: green;'>✓ Updated: $old_path → $new_path</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No records found for: $old_path</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error updating: $old_path</p>";
    }
}

// Verify the updates
echo "<h3>Verification:</h3>";
$result = $db->getMultipleRows($db->prepareAndExecute("SELECT clothes_id, product_name, image_path FROM tblClothes"));

foreach ($result as $product) {
    $image_path = $product['image_path'];
    $product_name = $product['product_name'];
    
    if (file_exists($image_path)) {
        echo "<p style='color: green;'>✓ {$product_name}: File exists at $image_path</p>";
    } else {
        echo "<p style='color: red;'>✗ {$product_name}: File NOT found at $image_path</p>";
    }
}

echo "<p><a href='products.php'>View Products Page</a></p>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>
