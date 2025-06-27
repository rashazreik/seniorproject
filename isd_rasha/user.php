<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .account-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1452780212940-6f5c0d14d848');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
            height: 100%;
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Account Header Section -->
<section class="account-header">
    <div class="container text-center">
        <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!</h1>
        <p class="lead">Manage your account, track orders, and explore our latest products</p>
    </div>
</section>

<!-- Dashboard Content -->
<section class="container mb-5">
    <div class="row">
        <!-- Account Summary Card -->
        <div class="col-lg-12 mb-4">
            <div class="card bg-light">
                <div class="card-body p-4">
                    <h4 class="card-title">Account Overview</h4>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION["full_name"]); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
                            
                            <?php
                            // Get user details from the database
                            $sql = "SELECT phone, address FROM users WHERE id = ?";
                            if($stmt = mysqli_prepare($conn, $sql)){
                                mysqli_stmt_bind_param($stmt, "i", $param_id);
                                $param_id = $_SESSION["id"];
                                
                                if(mysqli_stmt_execute($stmt)){
                                    mysqli_stmt_store_result($stmt);
                                    mysqli_stmt_bind_result($stmt, $phone, $address);
                                    if(mysqli_stmt_fetch($stmt)){
                                        echo "<p><strong>Phone:</strong> " . (empty($phone) ? "Not provided" : htmlspecialchars($phone)) . "</p>";
                                        echo "<p><strong>Address:</strong> " . (empty($address) ? "Not provided" : htmlspecialchars($address)) . "</p>";
                                    }
                                }
                                mysqli_stmt_close($stmt);
                            }
                            ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="edit_profile.php" class="btn btn-outline-primary mb-2">Edit Profile</a>
                            <a href="change_password.php" class="btn btn-outline-secondary mb-2">Change Password</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <h3 class="mb-4">Quick Actions</h3>
    
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-4">
            <div class="dashboard-card bg-white">
                <div class="text-primary card-icon">📦</div>
                <h4>My Orders</h4>
                <p>View and track your recent orders</p>
                <a href="orders.php" class="btn btn-primary">View Orders</a>
            </div>
        </div>
        
        <!-- Wishlist -->
        <div class="col-md-4">
            <div class="dashboard-card bg-white">
                <div class="text-danger card-icon">❤️</div>
                <h4>My Wishlist</h4>
                <p>Products you've saved for later</p>
                <a href="wishlist.php" class="btn btn-danger">View Wishlist</a>
            </div>
        </div>
        
        <!-- Browse Products -->
        <div class="col-md-4">
            <div class="dashboard-card bg-white">
                <div class="text-success card-icon">🔍</div>
                <h4>Browse Products</h4>
                <p>Explore our latest camera collection</p>
                <a href="products.php" class="btn btn-success">Shop Now</a>
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