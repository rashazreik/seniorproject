<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Save intended destination
    $_SESSION['redirect_after_login'] = 'wishlist.php';
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

// Handle add to wishlist
if(isset($_GET['add']) && !empty($_GET['add'])) {
    $product_id = $_GET['add'];
    $user_id = $_SESSION["id"];
    
    // Check if product exists
    $check_product_sql = "SELECT id FROM products WHERE id = ?";
    $check_product_stmt = mysqli_prepare($conn, $check_product_sql);
    mysqli_stmt_bind_param($check_product_stmt, "i", $product_id);
    mysqli_stmt_execute($check_product_stmt);
    mysqli_stmt_store_result($check_product_stmt);
    
    if(mysqli_stmt_num_rows($check_product_stmt) > 0) {
        // Add to wishlist (ignore if already exists)
        $add_sql = "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $add_stmt = mysqli_prepare($conn, $add_sql);
        mysqli_stmt_bind_param($add_stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($add_stmt);
        
        // Redirect to previous page or product details
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
        header("Location: $redirect");
        exit;
    }
}

// Handle remove from wishlist
if(isset($_GET['remove']) && !empty($_GET['remove'])) {
    $product_id = $_GET['remove'];
    $user_id = $_SESSION["id"];
    
    // Remove from wishlist
    $remove_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    $remove_stmt = mysqli_prepare($conn, $remove_sql);
    mysqli_stmt_bind_param($remove_stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($remove_stmt);
    
    // Redirect to wishlist page
    header("Location: wishlist.php");
    exit;
}

// Handle move to cart
if(isset($_GET['cart']) && !empty($_GET['cart'])) {
    $product_id = $_GET['cart'];
    $user_id = $_SESSION["id"];
    
    // Check if product exists and get stock quantity
    $check_product_sql = "SELECT id, stock_quantity FROM products WHERE id = ?";
    $check_product_stmt = mysqli_prepare($conn, $check_product_sql);
    mysqli_stmt_bind_param($check_product_stmt, "i", $product_id);
    mysqli_stmt_execute($check_product_stmt);
    $product_result = mysqli_stmt_get_result($check_product_stmt);
    
    if(mysqli_num_rows($product_result) > 0) {
        $product = mysqli_fetch_assoc($product_result);
        
        // Check if product is in stock
        if($product['stock_quantity'] > 0) {
            // Initialize cart if it doesn't exist
            if(!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            
            // Add to cart
            if(isset($_SESSION['cart'][$product_id])) {
                // Product already in cart, increment quantity
                $_SESSION['cart'][$product_id]++;
            } else {
                // Product not in cart, add it
                $_SESSION['cart'][$product_id] = 1;
            }
            
            // Remove from wishlist
            $remove_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
            $remove_stmt = mysqli_prepare($conn, $remove_sql);
            mysqli_stmt_bind_param($remove_stmt, "ii", $user_id, $product_id);
            mysqli_stmt_execute($remove_stmt);
            
            // Redirect to cart
            header("Location: cart.php");
            exit;
        }
    }
}

// Get user's wishlist items
$user_id = $_SESSION["id"];
$wishlist_items = [];

$sql = "SELECT p.*, w.added_at 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.added_at DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)) {
            $wishlist_items[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .wishlist-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .product-img {
            height: 150px;
            object-fit: contain;
        }
        .wishlist-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .wishlist-card:hover {
            transform: translateY(-5px);
        }
        .heart-icon {
            color: #dc3545;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Wishlist Header -->
<section class="wishlist-header">
    <div class="container text-center">
        <h1 class="display-4">My Wishlist</h1>
        <p class="lead">Products you've saved for later</p>
    </div>
</section>

<!-- Wishlist Section -->
<section class="container mb-5">
    <?php if(empty($wishlist_items)): ?>
        <div class="text-center py-5">
            <div class="heart-icon mb-3">♡</div>
            <h3>Your wishlist is empty</h3>
            <p class="mb-4">Browse our products and add items to your wishlist!</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($wishlist_items as $item): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card wishlist-card">
                        <div class="text-end p-2">
                            <a href="wishlist.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger" title="Remove from wishlist">
                                ✕
                            </a>
                        </div>
                        <div class="text-center p-3">
                            <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://via.placeholder.com/150x150?text=No+Image'; ?>" 
                                 class="product-img mb-3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="card-text fw-bold">$<?php echo number_format($item['price'], 2); ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Added on <?php echo date('M j, Y', strtotime($item['added_at'])); ?>
                                </small>
                            </p>
                            <div class="d-grid gap-2">
                                <a href="product_details.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-secondary btn-sm">View Details</a>
                                <?php if($item['stock_quantity'] > 0): ?>
                                    <a href="wishlist.php?cart=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm">Add to Cart</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-primary">Browse More Products</a>
        </div>
    <?php endif; ?>
</section>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>