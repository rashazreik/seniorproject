<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Check if product ID is set
if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    header("location: products.php");
    exit;
}

$product_id = $_POST['product_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate quantity
if ($quantity < 1) {
    $quantity = 1;
}

// Check if product exists and has sufficient stock
$sql = "SELECT id, stock_quantity FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("location: products.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Ensure quantity doesn't exceed stock
if ($quantity > $product['stock_quantity']) {
    $quantity = $product['stock_quantity'];
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add or update product in cart
if (isset($_SESSION['cart'][$product_id])) {
    // Product already in cart, update quantity
    $_SESSION['cart'][$product_id] += $quantity;
    
    // Ensure quantity doesn't exceed stock
    if ($_SESSION['cart'][$product_id] > $product['stock_quantity']) {
        $_SESSION['cart'][$product_id] = $product['stock_quantity'];
    }
} else {
    // Product not in cart, add it
    $_SESSION['cart'][$product_id] = $quantity;
}

// Redirect back to previous page or cart
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
header("Location: $redirect");
exit;
?>