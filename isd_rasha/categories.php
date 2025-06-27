<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Determine if user is logged in
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Get all categories
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .categories-header {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1542567455-cd733f23fbb1');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .category-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .category-card:hover {
            transform: translateY(-5px);
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

<!-- Categories Header -->
<section class="categories-header">
    <div class="container text-center">
        <h1 class="display-4">Product Categories</h1>
        <p class="lead">Browse our products by category</p>
    </div>
</section>

<!-- Categories Section -->
<section class="container mb-5">
    <div class="row">
        <?php
        // Check if there are any categories
        if (mysqli_num_rows($result) > 0) {
            while ($category = mysqli_fetch_assoc($result)) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card category-card">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                            <p class="text-muted"><?php echo $category['product_count']; ?> products</p>
                            <a href="products.php?category=<?php echo urlencode($category['name']); ?>" class="btn btn-primary">View Products</a>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">No categories found.</p></div>';
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