<?php
// Initialize the session
session_start();

// Include database connection
require_once 'includes/config.php';

// Determine if user is logged in
$is_logged_in = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;

// Check if product ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: products.php");
    exit;
}

$product_id = $_GET['id'];

// Get product details
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if product exists
if (mysqli_num_rows($result) == 0) {
    header("location: products.php");
    exit;
}

$product = mysqli_fetch_assoc($result);

// Get product images
$img_sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
$img_stmt = mysqli_prepare($conn, $img_sql);
mysqli_stmt_bind_param($img_stmt, "i", $product_id);
mysqli_stmt_execute($img_stmt);
$img_result = mysqli_stmt_get_result($img_stmt);

// Get product reviews
$review_sql = "SELECT pr.*, u.full_name 
                FROM product_reviews pr 
                JOIN users u ON pr.user_id = u.id 
                WHERE pr.product_id = ? 
                ORDER BY pr.created_at DESC";
$review_stmt = mysqli_prepare($conn, $review_sql);
mysqli_stmt_bind_param($review_stmt, "i", $product_id);
mysqli_stmt_execute($review_stmt);
$review_result = mysqli_stmt_get_result($review_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img {
            max-height: 400px;
            object-fit: contain;
        }
        .thumbnail {
            cursor: pointer;
            height: 80px;
            object-fit: cover;
        }
        .thumbnail.active {
            border: 2px solid #0d6efd;
        }
        .review-card {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .star-rating {
            color: #ffc107;
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

<!-- Product Details Section -->
<section class="container py-5">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category_name']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6 mb-4">
            <div class="text-center mb-3">
                <?php 
                $main_image = (!empty($product['image_url'])) ? $product['image_url'] : 'https://via.placeholder.com/600x400?text=No+Image';
                if (mysqli_num_rows($img_result) > 0) {
                    $first_image = mysqli_fetch_assoc($img_result);
                    mysqli_data_seek($img_result, 0); // Reset result pointer
                    $main_image = $first_image['image_url'];
                }
                ?>
                <img id="mainImage" src="<?php echo $main_image; ?>" class="img-fluid product-img rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <?php if (mysqli_num_rows($img_result) > 1): ?>
            <div class="d-flex justify-content-center flex-wrap">
                <?php while($image = mysqli_fetch_assoc($img_result)): ?>
                <div class="m-2">
                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>" 
                         class="thumbnail rounded <?php echo $image['is_primary'] ? 'active' : ''; ?>" 
                         onclick="changeMainImage(this.src)" 
                         alt="Product thumbnail">
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="mb-3">
                <h3 class="text-primary mb-0"><?php echo '$' . number_format($product['price'], 2); ?></h3>
                
                <?php if ($product['stock_quantity'] > 0): ?>
                <span class="badge bg-success">In Stock</span>
                <?php else: ?>
                <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <?php if ($product['stock_quantity'] > 0): ?>
            <form action="add_to_cart.php" method="post" class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="quantity" class="col-form-label">Quantity:</label>
                    </div>
                    <div class="col-auto">
                        <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                    </div>
                    <div class="col-auto">
                        <span class="form-text"><?php echo $product['stock_quantity']; ?> available</span>
                    </div>
                </div>
                
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="submit" class="btn btn-primary btn-lg mt-3">Add to Cart</button>
            </form>
            <?php endif; ?>
            
            <div class="d-flex">
                <form action="add_to_wishlist.php" method="post" class="me-2">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-outline-secondary">Add to Wishlist</button>
                </form>
                
                <?php if ($is_logged_in): ?>
                <a href="#reviewForm" class="btn btn-outline-dark">Write a Review</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-outline-dark">Login to Review</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="mt-5">
        <h3 class="mb-4">Customer Reviews</h3>
        
        <?php if (mysqli_num_rows($review_result) > 0): ?>
            <?php while($review = mysqli_fetch_assoc($review_result)): ?>
            <div class="review-card">
                <div class="d-flex justify-content-between">
                    <h5><?php echo htmlspecialchars($review['full_name']); ?></h5>
                    <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                </div>
                <div class="star-rating mb-2">
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        echo ($i <= $review['rating']) ? '★' : '☆';
                    }
                    ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
        
        <?php if ($is_logged_in): ?>
        <!-- Review Form -->
        <div id="reviewForm" class="mt-4 p-4 bg-light rounded">
            <h4>Write a Review</h4>
            <form action="submit_review.php" method="post">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select class="form-select" id="rating" name="rating" required>
                        <option value="">Select rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="review" class="form-label">Your Review</label>
                    <textarea class="form-control" id="review" name="review_text" rows="4" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    var thumbnails = document.getElementsByClassName('thumbnail');
    for (var i = 0; i < thumbnails.length; i++) {
        thumbnails[i].classList.remove('active');
        if (thumbnails[i].src === src) {
            thumbnails[i].classList.add('active');
        }
    }
}
</script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>