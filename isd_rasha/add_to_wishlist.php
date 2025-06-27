<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Save intended destination
    $_SESSION['redirect_after_login'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Create wishlist table if it doesn't exist
$create_wishlist_table = "CREATE TABLE IF NOT EXISTS wishlist (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)";

mysqli_query($conn, $create_wishlist_table);

// Check if product ID is set
if(!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    header("location: products.php");
    exit;
}

$product_id = $_POST['product_id'];
$user_id = $_SESSION["id"];

// Check if product exists
$sql = "SELECT id FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) === 0) {
    header("location: products.php");
    exit;
}

// Add to wishlist (ignore if already exists)
$add_sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
$add_stmt = mysqli_prepare($conn, $add_sql);
mysqli_stmt_bind_param($add_stmt, "ii", $user_id, $product_id);
mysqli_stmt_execute($add_stmt);

// Redirect back to previous page or product details
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'product_details.php?id=' . $product_id;
header("Location: $redirect");
exit;
?>
