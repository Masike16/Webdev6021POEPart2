<?php
/**
 * Add to Cart Handler for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Database-based cart addition with user isolation
 * Cart management inspired by Vinted (2023) shopping cart system
 * Following PHP.net (2022) database transaction best practices
 */

session_start();
require_once 'DBConn.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id === 0) {
    header('Location: products.php');
    exit;
}

$db = new DBConn();
$user_id = $_SESSION['user_id'];

// Check if product exists and is available
$product = $db->getSingleRow($db->prepareAndExecute(
    "SELECT clothes_id, product_name, status FROM tblClothes WHERE clothes_id = ?",
    "i",
    [$product_id]
));

if (!$product || $product['status'] !== 'available') {
    $_SESSION['cart_error'] = 'Product is not available';
    header('Location: products.php');
    exit;
}

// Check if item already in cart
$existing_item = $db->getSingleRow($db->prepareAndExecute(
    "SELECT cart_id, quantity FROM tblCart WHERE user_id = ? AND clothes_id = ?",
    "ii",
    [$user_id, $product_id]
));

if ($existing_item) {
    // Update quantity if less than 99
    if ($existing_item['quantity'] < 99) {
        $stmt = $db->prepareAndExecute(
            "UPDATE tblCart SET quantity = quantity + 1 WHERE cart_id = ?",
            "i",
            [$existing_item['cart_id']]
        );
        $_SESSION['cart_success'] = 'Item quantity updated in cart';
    } else {
        $_SESSION['cart_error'] = 'Maximum quantity reached for this item';
    }
} else {
    // Add new item to cart
    $stmt = $db->prepareAndExecute(
        "INSERT INTO tblCart (user_id, clothes_id, quantity) VALUES (?, ?, ?)",
        "iii",
        [$user_id, $product_id, 1]
    );
    
    if ($stmt) {
        $_SESSION['cart_success'] = 'Item added to cart successfully';
    } else {
        $_SESSION['cart_error'] = 'Failed to add item to cart';
    }
}

// Redirect back to product details or products page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
header("Location: $referer");
exit;
?>
