<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Save intended destination
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Check if cart is empty
if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("location: cart.php");
    exit;
}

// Get user information
$user_id = $_SESSION["id"];
$user_info = [];

$sql = "SELECT full_name, email, phone, address FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $user_info = mysqli_fetch_assoc($result);
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Initialize variables
$shipping_address = $user_info['address'] ?? '';
$payment_method = '';
$errors = [];
$cart_items = [];
$total = 0;

// Get cart items from database
if(!empty($_SESSION['cart'])) {
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
    
    while($product = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$product['id']];
        
        // Ensure quantity doesn't exceed stock
        if($quantity > $product['stock_quantity']) {
            $quantity = $product['stock_quantity'];
            $_SESSION['cart'][$product['id']] = $quantity;
        }
        
        $product['quantity'] = $quantity;
        $product['subtotal'] = $quantity * $product['price'];
        $total += $product['subtotal'];
        
        $cart_items[] = $product;
    }
}

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate shipping address
    if(empty(trim($_POST["shipping_address"]))) {
        $errors['shipping_address'] = "Please enter your shipping address.";
    } else {
        $shipping_address = trim($_POST["shipping_address"]);
    }
    
    // Validate payment method
    if(empty($_POST["payment_method"])) {
        $errors['payment_method'] = "Please select a payment method.";
    } else {
        $payment_method = $_POST["payment_method"];
    }
    
    // Create order if no errors
    if(empty($errors)) {
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert into orders table
            $order_sql = "INSERT INTO orders (user_id, status, total_amount, shipping_address, payment_status) 
                          VALUES (?, 'pending', ?, ?, ?)";
            
            $order_stmt = mysqli_prepare($conn, $order_sql);
            $payment_status = ($payment_method == 'cod') ? 'pending' : 'paid';
            
            mysqli_stmt_bind_param($order_stmt, "idss", $user_id, $total, $shipping_address, $payment_status);
            mysqli_stmt_execute($order_stmt);
            
            $order_id = mysqli_insert_id($conn);
            
            // Insert order items
            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                         VALUES (?, ?, ?, ?, ?)";
            
            $item_stmt = mysqli_prepare($conn, $item_sql);
            
            foreach($cart_items as $item) {
                mysqli_stmt_bind_param($item_stmt, "iiidi", $order_id, $item['id'], $item['quantity'], $item['price'], $item['subtotal']);
                mysqli_stmt_execute($item_stmt);
                
                // Update product stock
                $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $update_stock_stmt = mysqli_prepare($conn, $update_stock_sql);
                mysqli_stmt_bind_param($update_stock_stmt, "ii", $item['quantity'], $item['id']);
                mysqli_stmt_execute($update_stock_stmt);
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Redirect to order confirmation
            header("location: order_confirmation.php?id=$order_id");
            exit;
            
        } catch(Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            $errors['general'] = "An error occurred while processing your order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .checkout-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1607082349566-187342175e2f');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .product-img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Checkout Header -->
<section class="checkout-header">
    <div class="container text-center">
        <h1 class="display-4">Checkout</h1>
        <p class="lead">Complete your order</p>
    </div>
</section>

<!-- Checkout Section -->
<section class="container mb-5">
    <?php if(!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Order Summary -->
        <div class="col-md-5 order-md-2 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach($cart_items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://via.placeholder.com/60x60?text=No+Image'; ?>" 
                                     class="product-img me-2" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div>
                                    <h6 class="my-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></small>
                                </div>
                            </div>
                            <span class="text-muted">$<?php echo number_format($item['subtotal'], 2); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <strong>Free</strong>
                    </div>
                    
                    <hr class="my-2">
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total (USD)</span>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                    
                    <div class="d-grid">
                        <a href="cart.php" class="btn btn-outline-secondary">Edit Cart</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div class="col-md-7 order-md-1">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <h4 class="mb-3">Shipping Information</h4>
                
                <!-- Customer Information (read-only) -->
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" value="<?php echo htmlspecialchars($user_info['full_name']); ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" readonly>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control" id="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label for="shipping_address" class="form-label">Shipping Address</label>
                    <textarea class="form-control <?php echo (!empty($errors['shipping_address'])) ? 'is-invalid' : ''; ?>" 
                              id="shipping_address" name="shipping_address" rows="3" required><?php echo $shipping_address; ?></textarea>
                    <div class="invalid-feedback">
                        <?php echo $errors['shipping_address'] ?? ''; ?>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h4 class="mb-3">Payment Method</h4>
                
                <div class="my-3 <?php echo (!empty($errors['payment_method'])) ? 'is-invalid' : ''; ?>">
                    <div class="form-check mb-2">
                        <input id="credit" name="payment_method" type="radio" class="form-check-input" value="credit" 
                               <?php echo ($payment_method == 'credit') ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="credit">Credit Card</label>
                    </div>
                    <div class="form-check mb-2">
                        <input id="paypal" name="payment_method" type="radio" class="form-check-input" value="paypal"
                               <?php echo ($payment_method == 'paypal') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="paypal">PayPal</label>
                    </div>
                    <div class="form-check">
                        <input id="cod" name="payment_method" type="radio" class="form-check-input" value="cod"
                               <?php echo ($payment_method == 'cod') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="cod">Cash on Delivery</label>
                    </div>
                    <div class="invalid-feedback">
                        <?php echo $errors['payment_method'] ?? ''; ?>
                    </div>
                </div>
                
                <!-- Credit Card Form (shows only when credit card is selected) -->
                <div id="creditCardForm" class="d-none">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cc-name" class="form-label">Name on card</label>
                            <input type="text" class="form-control" id="cc-name" placeholder="Full name as displayed on card">
                            <small class="text-muted">Full name as displayed on card</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cc-number" class="form-label">Credit card number</label>
                            <input type="text" class="form-control" id="cc-number" placeholder="1234 5678 9012 3456">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="cc-expiration" class="form-label">Expiration</label>
                            <input type="text" class="form-control" id="cc-expiration" placeholder="MM/YY">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="cc-cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cc-cvv" placeholder="123">
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <button class="btn btn-primary btn-lg w-100" type="submit">Place Order</button>
            </form>
        </div>
    </div>
</section>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Show/hide credit card form based on payment method selection
$(document).ready(function() {
    $('input[name="payment_method"]').change(function() {
        if (this.value == 'credit') {
            $('#creditCardForm').removeClass('d-none');
        } else {
            $('#creditCardForm').addClass('d-none');
        }
    });
    
    // Trigger change event on page load
    $('input[name="payment_method"]:checked').trigger('change');
});
</script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>