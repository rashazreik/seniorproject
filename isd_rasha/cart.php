<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Determine if user is logged in
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Process quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// Process item removal
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit;
}

// Get cart items details from database
$cart_items = array();
$total = 0;

if (!empty($_SESSION['cart'])) {
    // Get product IDs from cart
    $product_ids = array_keys($_SESSION['cart']);
    
    // Create placeholders for the query
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $sql = "SELECT id, name, price, stock_quantity, image_url FROM products WHERE id IN ($placeholders)";
    $stmt = mysqli_prepare($conn, $sql);
    
    // Bind product IDs as parameters
    $types = str_repeat('i', count($_SESSION['cart']));
    mysqli_stmt_bind_param($stmt, $types, ...$product_ids);
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($product = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        
        // Ensure quantity doesn't exceed stock
        if ($quantity > $product['stock_quantity']) {
            $quantity = $product['stock_quantity'];
            $_SESSION['cart'][$product['id']] = $quantity;
        }
        
        $product['quantity'] = $quantity;
        $product['subtotal'] = $quantity * $product['price'];
        $total += $product['subtotal'];
        
        $cart_items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .product-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<!-- Include Navbar (based on login status) -->
<?php 
if ($is_logged_in) {
    include 'includes/navbar_user.php';
} else {
    include 'includes/navbar.php';
}
?>

<!-- Cart Header -->
<section class="cart-header">
    <div class="container text-center">
        <h1 class="display-4">Your Shopping Cart</h1>
        <p class="lead">Review and update your selected items</p>
    </div>
</section>

<!-- Cart Section -->
<section class="container mb-5">
    <?php if (empty($cart_items)): ?>
    <div class="text-center py-5">
        <h3>Your cart is empty</h3>
        <p class="mb-4">Looks like you haven't added any products to your cart yet.</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    </div>
    <?php else: ?>
    <form method="post" action="cart.php">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://via.placeholder.com/80x80?text=No+Image'; ?>" 
                                     class="product-img me-3" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Stock: <?php echo $item['stock_quantity']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo '$' . number_format($item['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                   value="<?php echo $item['quantity']; ?>" 
                                   min="0" max="<?php echo $item['stock_quantity']; ?>" 
                                   class="form-control" style="max-width: 80px;">
                        </td>
                        <td class="text-end"><?php echo '$' . number_format($item['subtotal'], 2); ?></td>
                        <td class="text-end">
                            <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger">
                                Remove
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total:</td>
                        <td class="text-end"><?php echo '$' . number_format($total, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
            <div>
                <button type="submit" name="update_cart" class="btn btn-secondary me-2">Update Cart</button>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </div>
    </form>
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