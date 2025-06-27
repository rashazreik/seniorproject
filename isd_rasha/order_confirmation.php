<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Check if order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: user.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION["id"];
$order_details = [];
$order_items = [];

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
if($order_stmt = mysqli_prepare($conn, $order_sql)) {
    mysqli_stmt_bind_param($order_stmt, "ii", $order_id, $user_id);
    
    if(mysqli_stmt_execute($order_stmt)) {
        $order_result = mysqli_stmt_get_result($order_stmt);
        
        if(mysqli_num_rows($order_result) == 1) {
            $order_details = mysqli_fetch_assoc($order_result);
            
            // Get order items
            $items_sql = "SELECT oi.*, p.name, p.image_url 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?";
            
            if($items_stmt = mysqli_prepare($conn, $items_sql)) {
                mysqli_stmt_bind_param($items_stmt, "i", $order_id);
                
                if(mysqli_stmt_execute($items_stmt)) {
                    $items_result = mysqli_stmt_get_result($items_stmt);
                    
                    while($item = mysqli_fetch_assoc($items_result)) {
                        $order_items[] = $item;
                    }
                }
                
                mysqli_stmt_close($items_stmt);
            }
        } else {
            // Order not found or doesn't belong to this user
            header("location: user.php");
            exit;
        }
    }
    
    mysqli_stmt_close($order_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .confirmation-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1519682577862-22b62b24e493');
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
        .check-icon {
            font-size: 3.5rem;
            color: #28a745;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Confirmation Header -->
<section class="confirmation-header">
    <div class="container text-center">
        <h1 class="display-4">Order Confirmed!</h1>
        <p class="lead">Thank you for your purchase</p>
    </div>
</section>

<!-- Confirmation Section -->
<section class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="check-icon">✓</div>
                        <h3 class="my-3">Your order has been placed successfully!</h3>
                        <p class="mb-0">Order #<?php echo $order_id; ?></p>
                        <p class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($order_details['order_date'])); ?></p>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-0">We've sent a confirmation email to your registered email address.</p>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 60px;"></th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : 'https://via.placeholder.com/60x60?text=No+Image'; ?>" 
                                             class="product-img rounded" alt="Product">
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">$<?php echo number_format($item['total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td class="text-end">$<?php echo number_format($order_details['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order Number:</strong> #<?php echo $order_id; ?></p>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order_details['order_date'])); ?></p>
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <span class="badge <?php echo ($order_details['status'] == 'pending') ? 'bg-warning' : 'bg-success'; ?>">
                                    <?php echo ucfirst($order_details['status']); ?>
                                </span>
                            </p>
                            <p class="mb-1">
                                <strong>Payment Status:</strong> 
                                <span class="badge <?php echo ($order_details['payment_status'] == 'paid') ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($order_details['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Address</h6>
                            <p><?php echo nl2br(htmlspecialchars($order_details['shipping_address'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="orders.php" class="btn btn-primary me-2">View All Orders</a>
                <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
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