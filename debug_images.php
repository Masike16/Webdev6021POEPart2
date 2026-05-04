<?php
/**
 * Debug Image Display Issues
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 */

echo "<h2>Image Debug Information</h2>";

// Test direct file access
$test_image = 'images/jackets/Vintage_Nike_Jacket.jpg';
echo "<h3>File System Check</h3>";
echo "<p>Testing file: $test_image</p>";

if (file_exists($test_image)) {
    echo "<p style='color: green;'>✓ File exists</p>";
    
    $file_size = filesize($test_image);
    echo "<p>File size: " . number_format($file_size) . " bytes</p>";
    
    $file_info = getimagesize($test_image);
    if ($file_info) {
        echo "<p>Image dimensions: {$file_info[0]}x{$file_info[1]}</p>";
        echo "<p>Image type: {$file_info['mime']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Not a valid image file</p>";
    }
    
    // Check file permissions
    $perms = fileperms($test_image);
    echo "<p>File permissions: " . decoct($perms) . "</p>";
    echo "<p>Readable: " . (is_readable($test_image) ? 'Yes' : 'No') . "</p>";
    
} else {
    echo "<p style='color: red;'>✗ File does not exist</p>";
}

// Test web server access
echo "<h3>Web Server Access Test</h3>";
echo "<p>Direct image URL test:</p>";
echo "<img src='$test_image' alt='Test Image' style='border: 2px solid red; max-width: 300px;'>";
echo "<p>If you see a broken image above, the web server cannot access the file.</p>";

// Check .htaccess files
echo "<h3>Configuration Check</h3>";
$htaccess_files = [
    '.htaccess',
    'images/.htaccess',
    'uploads/.htaccess'
];

foreach ($htaccess_files as $htaccess) {
    if (file_exists($htaccess)) {
        echo "<p style='color: orange;'>⚠ Found .htaccess: $htaccess</p>";
        echo "<pre>" . htmlspecialchars(file_get_contents($htaccess)) . "</pre>";
    } else {
        echo "<p style='color: green;'>✓ No .htaccess: $htaccess</p>";
    }
}

// Check database image paths
echo "<h3>Database Image Paths</h3>";
require_once 'DBConn.php';
$db = new DBConn();
$products = $db->getMultipleRows($db->prepareAndExecute("SELECT clothes_id, product_name, image_path FROM tblClothes"));

foreach ($products as $product) {
    $path = $product['image_path'];
    $name = $product['product_name'];
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>$name</strong><br>";
    echo "Path: $path<br>";
    echo "File exists: " . (file_exists($path) ? 'Yes' : 'No') . "<br>";
    echo "Readable: " . (is_readable($path) ? 'Yes' : 'No') . "<br>";
    
    // Test direct image output
    echo "<img src='$path' alt='$name' style='max-width: 100px; height: 50px; object-fit: cover; border: 1px solid red;'>";
    echo "</div>";
}

echo "<h3>Server Info</h3>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>PHP Current Working Directory: " . getcwd() . "</p>";

echo "<h3>Solutions</h3>";
echo "<p>If images are not showing, try these fixes:</p>";
echo "<ol>";
echo "<li>Check if WAMP server is running and accessible</li>";
echo "<li>Verify the project is in the correct WAMP www directory</li>";
echo "<li>Check if there are any .htaccess restrictions</li>";
echo "<li>Ensure file permissions allow web server access</li>";
echo "<li>Try accessing images directly via browser URL</li>";
echo "</ol>";

echo "<p><a href='products.php'>View Products Page</a></p>";
?>
