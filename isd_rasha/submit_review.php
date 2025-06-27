<?php
// Initialize the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    $user_id = $_SESSION['id'];
    
    // Basic validation
    if ($product_id <= 0 || $rating <= 0 || $rating > 5 || empty($review_text)) {
        header("location: product_details.php?id=$product_id&error=invalid_input");
        exit;
    }
    
    // Check if product exists
    $check_sql = "SELECT id FROM products WHERE id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $product_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) == 0) {
        header("location: products.php");
        exit;
    }
    
    // Check if user already reviewed this product
    $existing_sql = "SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?";
    $existing_stmt = mysqli_prepare($conn, $existing_sql);
    mysqli_stmt_bind_param($existing_stmt, "ii", $product_id, $user_id);
    mysqli_stmt_execute($existing_stmt);
    mysqli_stmt_store_result($existing_stmt);
    
    if (mysqli_stmt_num_rows($existing_stmt) > 0) {
        // Update existing review
        $update_sql = "UPDATE product_reviews SET rating = ?, review_text = ?, created_at = NOW() WHERE product_id = ? AND user_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "isii", $rating, $review_text, $product_id, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            header("location: product_details.php?id=$product_id&success=review_updated");
        } else {
            header("location: product_details.php?id=$product_id&error=db_error");
        }
    } else {
        // Insert new review
        $insert_sql = "INSERT INTO product_reviews (product_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iiis", $product_id, $user_id, $rating, $review_text);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            header("location: product_details.php?id=$product_id&success=review_added");
        } else {
            header("location: product_details.php?id=$product_id&error=db_error");
        }
    }
} else {
    // Not a POST request, redirect to products page
    header("location: products.php");
}

// Close connection
mysqli_close($conn);
?>