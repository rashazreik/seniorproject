<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Get featured products from database (latest 3 products)
$featured_products = [];
$sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 3";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($product = mysqli_fetch_assoc($result)) {
        $featured_products[] = $product;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom inline styles -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=1000');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        
        .featured-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        
        .about-section {
            padding: 80px 0;
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-3 fw-bold">Capture Perfect Moments</h1>
        <p class="lead">Find the perfect camera for your photography journey</p>
        <a href="#featured" class="btn btn-light btn-lg mt-3">Shop Now</a>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="featured-section">
    <div class="container">
        <h2 class="text-center mb-5">Featured Products</h2>
        <div class="row">
            <?php if (!empty($featured_products)): ?>
                <?php foreach ($featured_products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100) . (strlen($product['description']) > 100 ? '...' : '')); ?></p>
                                <p class="fw-bold">$<?php echo number_format($product['price'], 2); ?></p>
                                <div class="d-grid">
                                    <a href="login.php" class="btn btn-primary">Login to View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No featured products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-4">About Our Store</h2>
                <p>Welcome to our premium camera shop where photography enthusiasts and professionals find the best equipment for their creative needs. We've been in business for over 15 years, providing high-quality cameras and accessories.</p>
                <p>Our team consists of passionate photographers who understand your needs and can offer expert advice on choosing the right equipment for your specific requirements.</p>
                <a href="about.php" class="btn btn-outline-dark mt-3">Learn More</a>
            </div>
            <div class="col-lg-6">
              <img src="https://images.unsplash.com/photo-1472898965229-f9b06b9c9bbe" class="img-fluid rounded" alt="Store Interior">
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

<!-- Custom JS -->
<script>
    $(document).ready(function() {
        // Smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(event) {
            var target = $(this.getAttribute('href'));
            if(target.length) {
                event.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 70
                }, 800);
            }
        });
    });
</script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>