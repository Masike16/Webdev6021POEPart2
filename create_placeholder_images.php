<?php
/**
 * Creates the Placeholder Images Script
 * Generates simple placeholder images for missing product images
 */

echo "<h2>Creating Placeholder Images</h2>";

$placeholder_dir = 'images';
$categories = ['shirts', 'pants', 'jackets', 'shoes', 'accessories'];
$colors = [
    'shirts' => [100, 150, 200],   // Blue
    'pants' => [150, 100, 200],    // Purple  
    'jackets' => [200, 150, 100],  // Orange
    'shoes' => [100, 200, 150],    // Green
    'accessories' => [200, 100, 100] // Red
];

$category_images = [
    'shirts' => ['adidas_shirt.jpg', 'tommy_shirt.jpg', 'champion_shirt.jpg'],
    'pants' => ['levis_jeans.jpg'],
    'jackets' => ['nike_jacket.jpg'],
    'shoes' => ['converse_shoes.jpg', 'docmartens_shoes.jpg'],
    'accessories' => ['rayban_accessories.jpg']
];

foreach ($categories as $category) {
    $category_dir = $placeholder_dir . '/' . $category;
    if (!is_dir($category_dir)) {
        mkdir($category_dir, 0755, true);
        echo "<p style='color: green;'>✓ Created directory: $category_dir</p>";
    }
    
    if (isset($category_images[$category])) {
        foreach ($category_images[$category] as $image_name) {
            $image_path = $category_dir . '/' . $image_name;
            
            // Create a simple SVG placeholder
            $rgb = $colors[$category];
            $svg_content = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
    <rect width="300" height="300" fill="rgb(' . implode(',', $rgb) . ')"/>
    <text x="150" y="150" font-family="Arial" font-size="20" fill="white" text-anchor="middle">
        ' . ucfirst($category) . '
    </text>
    <text x="150" y="180" font-family="Arial" font-size="14" fill="white" text-anchor="middle">
        ' . str_replace('_', ' ', pathinfo($image_name, PATHINFO_FILENAME)) . '
    </text>
</svg>';
            
            // Save as SVG file (more compatible than generating actual images)
            $svg_path = str_replace('.jpg', '.svg', $image_path);
            file_put_contents($svg_path, $svg_content);
            echo "<p style='color: green;'>✓ Created placeholder: $svg_path</p>";
        }
    }
}

echo "<h3>Placeholder Images Created</h3>";
echo "<p>SVG placeholders have been created for all product categories.</p>";
echo "<p><a href='fix_image_paths.php'>Fix Database Image Paths</a></p>";
echo "<p><a href='products.php'>View Products</a></p>";
?>
