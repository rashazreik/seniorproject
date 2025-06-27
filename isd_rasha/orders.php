<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Get user's orders
$user_id = $_SESSION["id"];
$orders = [];

$sql = "SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.order_date DESC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)){
            $orders[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Handle order details request
$order_details = [];
$order_items = [];

if(isset($_GET['id']) && !empty($_GET['id'])){
    $order_id = trim($_GET['id']);
    
    // Get order details
    $details_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    if($details_stmt = mysqli_prepare($conn, $details_sql)){
        mysqli_stmt_bind_param($details_stmt, "ii", $order_id, $user_id);
        
        if(mysqli_stmt_execute($details_stmt)){
            $details_result = mysqli_stmt_get_result($details_stmt);
            
            if(mysqli_num_rows($details_result) == 1){
                $order_details = mysqli_fetch_assoc($details_result);
                
                // Get order items
                $items_sql = "SELECT oi.*, p.name, p.image_url 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?";
                
                if($items_stmt = mysqli_prepare($conn, $items_sql)){
                    mysqli_stmt_bind_param($items_stmt, "i", $order_id);
                    
                    if(mysqli_stmt_execute($items_stmt)){
                        $items_result = mysqli_stmt_get_result($items_stmt);
                        
                        while($item = mysqli_fetch_assoc($items_result)){
                            $order_items[] = $item;
                        }
                    }
                    
                    mysqli_stmt_close($items_stmt);
                }
            }
        }
        
        mysqli_stmt_close($details_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .orders-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .order-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Orders Header -->
<section class="orders-header">
    <div class="container text-center">
        <h1 class="display-4">My Orders</h1>
        <p class="lead">Track and manage your purchases</p>
    </div>
</section>

<!-- Orders Section -->
<section class="container mb-5">
    <?php if(!empty($order_details)): ?>
        <!-- Order Details View -->
        <div class="mb-4">
            <a href="orders.php" class="btn btn-outline-secondary mb-3">
                &laquo; Back to All Orders
            </a>
            
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Order #<?php echo $order_details['id']; ?></h5>
                        <div>
                            <?php
                            $status_class = '';
                            switch($order_details['status']) {
                                case 'pending':
                                    $status_class = 'bg-warning';
                                    break;
                                case 'processing':
                                    $status_class = 'bg-info';
                                    break;
                                case 'shipped':
                                    $status_class = 'bg-primary';
                                    break;
                                case 'delivered':
                                    $status_class = 'bg-success';
                                    break;
                                case 'cancelled':
                                    $status_class = 'bg-danger';
                                    break;
                                default:
                                    $status_class = 'bg-secondary';
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?> status-badge">
                                <?php echo ucfirst($order_details['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order_details['order_date'])); ?></p>
                            <p class="mb-1"><strong>Status:</strong> <?php echo ucfirst($order_details['status']); ?></p>
                            <p class="mb-1"><strong>Payment Status:</strong> <?php echo ucfirst($order_details['payment_status']); ?></p>
                            <p class="mb-1"><strong>Total:</strong> $<?php echo number_format($order_details['total_amount'], 2); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Address</h6>
                            <p><?php echo nl2br(htmlspecialchars($order_details['shipping_address'])); ?></p>
                        </div>
                    </div>
                    
                    <h6>Order Items</h6>
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
        </div>
    <?php else: ?>
        <!-- Orders List View -->
        <h3 class="mb-4">Order History</h3>
        
        <?php if(empty($orders)): ?>
            <div class="alert alert-info">
                <p class="mb-0">You haven't placed any orders yet.</p>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr class="order-card" onclick="window.location='orders.php?id=<?php echo $order['id']; ?>'">
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['item_count']; ?> items</td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch($order['status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning';
                                        break;
                                    case 'processing':
                                        $status_class = 'bg-info';
                                        break;
                                    case 'shipped':
                                        $status_class = 'bg-primary';
                                        break;
                                    case 'delivered':
                                        $status_class = 'bg-success';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'bg-danger';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?> status-badge">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?> status-badge">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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