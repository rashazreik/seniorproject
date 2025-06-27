<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Determine if user is logged in
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Get category filter if exists
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build the query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id";

// Add category filter if specified
if (!empty($category_filter)) {
    $sql .= " WHERE c.name = ?";
}

// Add sorting
$sql .= " ORDER BY p.created_at DESC";

// Prepare and execute the query
$stmt = mysqli_prepare($conn, $sql);

// Bind parameters if needed
if (!empty($category_filter)) {
    mysqli_stmt_bind_param($stmt, "s", $category_filter);
}

// Execute the query
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .products-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1500634245200-e5245c7574ef');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .product-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Include Navbar (based on login status) -->
<?php 
    include 'includes/navbar_user.php';

?>

<!-- Products Header -->
<section class="products-header">
    <div class="container text-center">
        <h1 class="display-4">Our Products</h1>
        <p class="lead">Find the perfect camera for your photography needs</p>
    </div>
</section>

<!-- Products Section -->
<section class="container mb-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Browse Our Collection</h2>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-md-end">
                <!-- Category Filter -->
                <?php
                // Get all categories
                $cat_sql = "SELECT * FROM categories ORDER BY name";
                $cat_result = mysqli_query($conn, $cat_sql);
                ?>
                <form method="get" action="products.php" class="me-2">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php while ($category = mysqli_fetch_assoc($cat_result)): ?>
                            <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                <?php echo ($category_filter == $category['name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
                
                <!-- Sort dropdown could be added here -->
            </div>
        </div>
    </div>
    
    <div class="row">
        <?php
        // Check if there are any products
        if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card product-card">
                        <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                             class="card-img-top product-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
                            <p class="card-text fw-bold"><?php echo '$' . number_format($product['price'], 2); ?></p>
                            <div class="d-flex justify-content-between">
                                <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                <form action="add_to_cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">No products found.</p></div>';
        }
        ?>
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