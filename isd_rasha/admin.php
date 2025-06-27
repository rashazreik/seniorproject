<?php
// Start session
session_start();

// Include database configuration
require_once 'includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT * FROM admins WHERE id = $admin_id";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Update admin last login time
$update_login = "UPDATE admins SET last_login = NOW() WHERE id = $admin_id";
mysqli_query($conn, $update_login);

// Handle delete operations
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['table'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $table = mysqli_real_escape_string($conn, $_GET['table']);
    
    // Check which table to delete from
    switch ($table) {
        case 'products':
            $delete_query = "DELETE FROM products WHERE id = $id";
            break;
        case 'users':
            $delete_query = "DELETE FROM users WHERE id = $id";
            break;
        case 'orders':
            $delete_query = "DELETE FROM orders WHERE id = $id";
            break;
case 'reviews':
    $delete_query = "DELETE FROM product_reviews WHERE id = $id";
    break;
        case 'categories':
            $delete_query = "DELETE FROM categories WHERE id = $id";
            break;
        case 'admins':
            // Prevent deleting self
            if ($id == $admin_id) {
                $_SESSION['error'] = "You cannot delete your own admin account.";
                break;
            }
            $delete_query = "DELETE FROM admins WHERE id = $id";
            break;
        case 'contacts':
            $delete_query = "DELETE FROM contacts WHERE id = $id";
            break;
    }
    
    if (isset($delete_query)) {
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = ucfirst($table) . " item deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting item: " . mysqli_error($conn);
        }
    }
    
    // Redirect to remove the GET parameters
    header("Location: admin.php?section=$table");
    exit();
}

// Handle contact message status update
if (isset($_GET['mark_as']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['mark_as']);
    
    $update_query = "UPDATE contacts SET status = '$status', updated_at = NOW() WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Message marked as $status.";
    } else {
        $_SESSION['error'] = "Error updating message status: " . mysqli_error($conn);
    }
    
    // Redirect to remove the GET parameters
    header("Location: admin.php?section=contacts");
    exit();
}

// Handle order status update
if (isset($_POST['update_order_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
    
    $update_query = "UPDATE orders SET status = '$status', payment_status = '$payment_status' WHERE id = $order_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Order status updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating order status: " . mysqli_error($conn);
    }
    
    // Redirect to remove the POST parameters
    header("Location: admin.php?section=orders");
    exit();
}

// Default section
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Add new product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    
    // Get category name
    $cat_query = "SELECT name FROM categories WHERE id = $category_id";
    $cat_result = mysqli_query($conn, $cat_query);
    $cat_row = mysqli_fetch_assoc($cat_result);
    $category_name = $cat_row['name'];
    
    $insert_query = "INSERT INTO products (name, description, price, stock_quantity, category, image_url, created_by, category_id) 
                     VALUES ('$name', '$description', $price, $stock, '$category_name', '$image_url', $admin_id, $category_id)";
    
    if (mysqli_query($conn, $insert_query)) {
        // Get the product ID
        $product_id = mysqli_insert_id($conn);
        
        // Add product image
        $image_insert = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES ($product_id, '$image_url', 1)";
        mysqli_query($conn, $image_insert);
        
        $_SESSION['success'] = "Product added successfully.";
    } else {
        $_SESSION['error'] = "Error adding product: " . mysqli_error($conn);
    }
    
    // Redirect to remove the POST parameters
    header("Location: admin.php?section=products");
    exit();
}

// Add new category
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $insert_query = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
    
    if (mysqli_query($conn, $insert_query)) {
        $_SESSION['success'] = "Category added successfully.";
    } else {
        $_SESSION['error'] = "Error adding category: " . mysqli_error($conn);
    }
    
    // Redirect to remove the POST parameters
    header("Location: admin.php?section=categories");
    exit();
}

// Edit product
if (isset($_POST['edit_product'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    
    // Get category name
    $cat_query = "SELECT name FROM categories WHERE id = $category_id";
    $cat_result = mysqli_query($conn, $cat_query);
    $cat_row = mysqli_fetch_assoc($cat_result);
    $category_name = $cat_row['name'];
    
    $update_query = "UPDATE products SET 
                     name = '$name', 
                     description = '$description', 
                     price = $price, 
                     stock_quantity = $stock, 
                     category = '$category_name', 
                     image_url = '$image_url', 
                     updated_by = $admin_id,
                     category_id = $category_id
                     WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
        // Update product image
        $check_image = "SELECT id FROM product_images WHERE product_id = $id AND is_primary = 1";
        $img_result = mysqli_query($conn, $check_image);
        
        if (mysqli_num_rows($img_result) > 0) {
            $img_row = mysqli_fetch_assoc($img_result);
            $img_id = $img_row['id'];
            $update_image = "UPDATE product_images SET image_url = '$image_url' WHERE id = $img_id";
            mysqli_query($conn, $update_image);
        } else {
            $insert_image = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES ($id, '$image_url', 1)";
            mysqli_query($conn, $insert_image);
        }
        
        $_SESSION['success'] = "Product updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating product: " . mysqli_error($conn);
    }
    
    // Redirect to remove the POST parameters
    header("Location: admin.php?section=products");
    exit();
}

// Edit category
if (isset($_POST['edit_category'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $update_query = "UPDATE categories SET name = '$name', description = '$description' WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
        // Update category name in products
        $update_products = "UPDATE products SET category = '$name' WHERE category_id = $id";
        mysqli_query($conn, $update_products);
        
        $_SESSION['success'] = "Category updated successfully.";
    } else {
        $_SESSION['error'] = "Error updating category: " . mysqli_error($conn);
    }
    
    // Redirect to remove the POST parameters
    header("Location: admin.php?section=categories");
    exit();
}

// Fetch data for dashboard
$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$total_products = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products"));
$total_orders = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM orders"));
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'"))['revenue'] ?? 0;
$low_stock = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products WHERE stock_quantity < 10"));
$recent_orders = mysqli_query($conn, "SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5");
$unread_messages = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM contacts WHERE status = 'unread'"));

// Get recent products
$recent_products = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 5");

// Get top selling products
$top_products = mysqli_query($conn, "
    SELECT p.*, SUM(oi.quantity) as total_sold 
    FROM products p 
    JOIN order_items oi ON p.id = oi.product_id 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Camera Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
        }
        .sidebar a {
            color: #f8f9fa;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            transition: background-color 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .sidebar-heading {
            color: #adb5bd;
            padding: 10px 15px;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .content {
            padding: 20px;
        }
        .card-dashboard {
            border-left: 4px solid #007bff;
        }
        .card-dashboard.revenue {
            border-left-color: #28a745;
        }
        .card-dashboard.users {
            border-left-color: #17a2b8;
        }
        .card-dashboard.orders {
            border-left-color: #ffc107;
        }
        .card-dashboard.stock {
            border-left-color: #dc3545;
        }
        .icon-bg {
            font-size: 4rem;
            opacity: 0.3;
            position: absolute;
            right: 15px;
            top: 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="sidebar-heading">Camera Store</div>
                <a href="admin.php" class="<?php echo $section == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="admin.php?section=products" class="<?php echo $section == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-camera me-2"></i> Products
                </a>
                <a href="admin.php?section=categories" class="<?php echo $section == 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-list me-2"></i> Categories
                </a>
                <a href="admin.php?section=orders" class="<?php echo $section == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
                <a href="admin.php?section=users" class="<?php echo $section == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Users
                </a>
                <a href="admin.php?section=contacts" class="<?php echo $section == 'contacts' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope me-2"></i> Contact Messages
                    <?php if ($unread_messages > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?section=admins" class="<?php echo $section == 'admins' ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield me-2"></i> Admins
                </a>
<a href="admin.php?section=reviews" class="<?php echo $section == 'reviews' ? 'active' : ''; ?>">
    <i class="fas fa-star me-2"></i> Reviews
</a>
                <div class="mt-5">
                    <a href="admin.php?logout=1" class="text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <?php
                        switch ($section) {
                            case 'dashboard':
                                echo "Dashboard";
                                break;
                            case 'products':
                                echo "Products Management";
                                break;
                            case 'categories':
                                echo "Categories Management";
                                break;
                            case 'orders':
                                echo "Orders Management";
                                break;
                            case 'users':
                                echo "Users Management";
                                break;
                            case 'contacts':
                                echo "Contact Messages";
                                break;
                            case 'admins':
                                echo "Admin Management";
                                break;
case 'reviews':
    echo "Reviews Management";
    break;
                            default:
                                echo "Dashboard";
                        }
                        ?>
                    </h2>
                    <div class="text-end">
                        <span class="text-muted me-2">Welcome,</span>
                        <span class="fw-bold"><?php echo $admin['username']; ?></span>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                // Display different sections based on selection
                switch ($section) {
                    case 'dashboard':
                        // Dashboard content
                        ?>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card card-dashboard">
                                    <div class="card-body position-relative">
                                        <h5 class="card-title">Total Products</h5>
                                        <h2 class="card-text"><?php echo $total_products; ?></h2>
                                        <i class="fas fa-camera icon-bg text-primary"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-dashboard users">
                                    <div class="card-body position-relative">
                                        <h5 class="card-title">Total Users</h5>
                                        <h2 class="card-text"><?php echo $total_users; ?></h2>
                                        <i class="fas fa-users icon-bg text-info"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-dashboard orders">
                                    <div class="card-body position-relative">
                                        <h5 class="card-title">Total Orders</h5>
                                        <h2 class="card-text"><?php echo $total_orders; ?></h2>
                                        <i class="fas fa-shopping-cart icon-bg text-warning"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card card-dashboard revenue">
                                    <div class="card-body position-relative">
                                        <h5 class="card-title">Total Revenue</h5>
                                        <h2 class="card-text">$<?php echo number_format($total_revenue, 2); ?></h2>
                                        <i class="fas fa-dollar-sign icon-bg text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-shopping-cart me-1"></i>
                                        Recent Orders
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Customer</th>
                                                        <th>Date</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                                        <tr>
                                                            <td>#<?php echo $order['id']; ?></td>
                                                            <td><?php echo $order['full_name']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                            <td>
                                                                <?php
                                                                switch ($order['status']) {
                                                                    case 'pending':
                                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                                        break;
                                                                    case 'processing':
                                                                        echo '<span class="badge bg-info">Processing</span>';
                                                                        break;
                                                                    case 'shipped':
                                                                        echo '<span class="badge bg-primary">Shipped</span>';
                                                                        break;
                                                                    case 'completed':
                                                                        echo '<span class="badge bg-success">Completed</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-camera me-1"></i>
                                        Top Selling Products
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Category</th>
                                                        <th>Price</th>
                                                        <th>Units Sold</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($product = mysqli_fetch_assoc($top_products)): ?>
                                                        <tr>
                                                            <td><?php echo $product['name']; ?></td>
                                                            <td><?php echo $product['category']; ?></td>
                                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                            <td><?php echo $product['total_sold']; ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Low Stock Products (Less than 10)
                                    </div>
                                    <div class="card-body">
                                        <?php if ($low_stock > 0): ?>
                                            <div class="alert alert-warning">
                                                <strong><?php echo $low_stock; ?> products</strong> have low stock levels.
                                                <a href="admin.php?section=products" class="alert-link">View details</a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-success">
                                                All products have sufficient stock levels.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;
                        
                    case 'products':
                        // Products Management
                        
                        // Get all products or search results
                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                        $filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
                        
                        $where_clause = "";
                        if (!empty($search)) {
                            $where_clause .= " WHERE p.name LIKE '%$search%' OR p.description LIKE '%$search%'";
                        }
                        
                        if ($filter_category > 0) {
                            $where_clause = empty($where_clause) ? " WHERE p.category_id = $filter_category" : $where_clause . " AND p.category_id = $filter_category";
                        }
                        
                        $query = "SELECT p.*, c.name as category_name FROM products p 
                                  LEFT JOIN categories c ON p.category_id = c.id
                                  $where_clause
                                  ORDER BY p.id DESC";
                        $products_result = mysqli_query($conn, $query);
                        
                        // Get categories for filter and form
                        $categories_query = "SELECT * FROM categories ORDER BY name";
                        $categories_result = mysqli_query($conn, $categories_query);
                        $categories = [];
                        while ($cat = mysqli_fetch_assoc($categories_result)) {
                            $categories[] = $cat;
                        }
                        
                        // Get product for editing if needed
                        $edit_product = null;
                        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
                            $edit_id = (int)$_GET['edit'];
                            $edit_query = "SELECT * FROM products WHERE id = $edit_id";
                            $edit_result = mysqli_query($conn, $edit_query);
                            $edit_product = mysqli_fetch_assoc($edit_result);
                        }
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <form method="GET" action="" class="d-flex">
                                            <input type="hidden" name="section" value="products">
                                            <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo $search; ?>">
                                            <select name="category" class="form-select me-2" style="width: 200px;">
                                                <option value="0">All Categories</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo $cat['id']; ?>" <?php echo $filter_category == $cat['id'] ? 'selected' : ''; ?>>
                                                        <?php echo $cat['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary">Search</button>
                                            <?php if (!empty($search) || $filter_category > 0): ?>
                                                <a href="admin.php?section=products" class="btn btn-secondary ms-2">Clear</a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                            <i class="fas fa-plus me-1"></i> Add New Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Image</th>
                                                <th>Category</th>
                                                <th>Price</th>
                                                <th>Stock</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($products_result) > 0): ?>
                                                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                                    <tr>
                                                        <td><?php echo $product['id']; ?></td>
                                                        <td><?php echo $product['name']; ?></td>
                                                        <td>
                                                            <?php if (!empty($product['image_url'])): ?>
                                                                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <span class="text-muted">No image</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $product['category']; ?></td>
                                                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                        <td>
                                                            <?php if ($product['stock_quantity'] < 10): ?>
                                                                <span class="text-danger fw-bold"><?php echo $product['stock_quantity']; ?></span>
                                                            <?php else: ?>
                                                                <?php echo $product['stock_quantity']; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="admin.php?section=products&edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary me-1">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="admin.php?section=products&delete=1&id=<?php echo $product['id']; ?>&table=products" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No products found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add Product Modal -->
                        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Product Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="price" class="form-label">Price</label>
                                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="stock" class="form-label">Stock Quantity</label>
                                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category</label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="image_url" class="form-label">Image URL</label>
                                                <input type="url" class="form-control" id="image_url" name="image_url">
                                                <small class="text-muted">Enter a valid URL for the product image</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Product Modal -->
                        <?php if ($edit_product): ?>
                            <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                                            <a href="admin.php?section=products" class="btn-close"></a>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="edit_name" class="form-label">Product Name</label>
                                                    <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo $edit_product['name']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_description" class="form-label">Description</label>
                                                    <textarea class="form-control" id="edit_description" name="description" rows="3"><?php echo $edit_product['description']; ?></textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_price" class="form-label">Price</label>
                                                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" value="<?php echo $edit_product['price']; ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="edit_stock" class="form-label">Stock Quantity</label>
                                                        <input type="number" class="form-control" id="edit_stock" name="stock" min="0" value="<?php echo $edit_product['stock_quantity']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_category_id" class="form-label">Category</label>
                                                    <select class="form-select" id="edit_category_id" name="category_id" required>
                                                        <option value="">Select Category</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?php echo $cat['id']; ?>" <?php echo $edit_product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                                <?php echo $cat['name']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_image_url" class="form-label">Image URL</label>
                                                    <input type="url" class="form-control" id="edit_image_url" name="image_url" value="<?php echo $edit_product['image_url']; ?>">
                                                    <?php if (!empty($edit_product['image_url'])): ?>
                                                        <div class="mt-2">
                                                            <img src="<?php echo $edit_product['image_url']; ?>" alt="<?php echo $edit_product['name']; ?>" style="max-width: 100px; max-height: 100px;">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="admin.php?section=products" class="btn btn-secondary">Cancel</a>
                                                <button type="submit" name="edit_product" class="btn btn-primary">Update Product</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                                    editModal.show();
                                });
                            </script>
                        <?php endif; ?>
                        <?php
                        break;
                        
                    case 'categories':
                        // Categories Management
                        
                        // Get all categories
                        $categories_query = "SELECT * FROM categories ORDER BY name";
                        $categories_result = mysqli_query($conn, $categories_query);
                        
                        // Get category for editing if needed
                        $edit_category = null;
                        if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
                            $edit_id = (int)$_GET['edit'];
                            $edit_query = "SELECT * FROM categories WHERE id = $edit_id";
                            $edit_result = mysqli_query($conn, $edit_query);
                            $edit_category = mysqli_fetch_assoc($edit_result);
                        }
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0">Categories List</h5>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                            <i class="fas fa-plus me-1"></i> Add New Category
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Products</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($categories_result) > 0): ?>
                                                <?php while ($category = mysqli_fetch_assoc($categories_result)): 
                                                    // Count products in this category
                                                    $cat_id = $category['id'];
                                                    $product_count_query = "SELECT COUNT(*) as count FROM products WHERE category_id = $cat_id";
                                                    $product_count_result = mysqli_query($conn, $product_count_query);
                                                    $product_count = mysqli_fetch_assoc($product_count_result)['count'];
                                                ?>
                                                    <tr>
                                                        <td><?php echo $category['id']; ?></td>
                                                        <td><?php echo $category['name']; ?></td>
                                                        <td><?php echo substr($category['description'], 0, 100) . (strlen($category['description']) > 100 ? '...' : ''); ?></td>
                                                        <td><?php echo $product_count; ?></td>
                                                        <td>
                                                            <a href="admin.php?section=categories&edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary me-1">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($product_count == 0): ?>
                                                                <a href="admin.php?section=categories&delete=1&id=<?php echo $category['id']; ?>&table=categories" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-danger" disabled title="Cannot delete category with products">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No categories found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Add Category Modal -->
                        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="category_name" class="form-label">Category Name</label>
                                                <input type="text" class="form-control" id="category_name" name="name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category_description" class="form-label">Description</label>
                                                <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Edit Category Modal -->
                        <?php if ($edit_category): ?>
                            <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true" data-bs-backdrop="static">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                                            <a href="admin.php?section=categories" class="btn-close"></a>
                                        </div>
                                        <form method="POST" action="">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="edit_category_name" class="form-label">Category Name</label>
                                                    <input type="text" class="form-control" id="edit_category_name" name="name" value="<?php echo $edit_category['name']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_category_description" class="form-label">Description</label>
                                                    <textarea class="form-control" id="edit_category_description" name="description" rows="3"><?php echo $edit_category['description']; ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="admin.php?section=categories" class="btn btn-secondary">Cancel</a>
                                                <button type="submit" name="edit_category" class="btn btn-primary">Update Category</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    var editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                                    editModal.show();
                                });
                            </script>
                        <?php endif; ?>
                        <?php
                        break;
                        
                    case 'orders':
                        // Orders Management
                        
                        // Get all orders or filter by status
                        $filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                        
                        $where_clause = "";
                        if (!empty($filter_status)) {
                            $where_clause = " WHERE o.status = '$filter_status'";
                        }
                        
                        $query = "SELECT o.*, u.full_name, u.email, u.phone 
                                  FROM orders o 
                                  JOIN users u ON o.user_id = u.id
                                  $where_clause
                                  ORDER BY o.order_date DESC";
                        $orders_result = mysqli_query($conn, $query);
                        
                        // Get order details if viewing a specific order
                        $view_order = null;
                        $order_items = null;
                        if (isset($_GET['view']) && is_numeric($_GET['view'])) {
                            $view_id = (int)$_GET['view'];
                            $view_query = "SELECT o.*, u.full_name, u.email, u.phone, u.address 
                                          FROM orders o 
                                          JOIN users u ON o.user_id = u.id 
                                          WHERE o.id = $view_id";
                            $view_result = mysqli_query($conn, $view_query);
                            $view_order = mysqli_fetch_assoc($view_result);
                            
                            // Get order items
                            $items_query = "SELECT oi.*, p.name, p.image_url 
                                           FROM order_items oi 
                                           JOIN products p ON oi.product_id = p.id 
                                           WHERE oi.order_id = $view_id";
                            $order_items = mysqli_query($conn, $items_query);
                        }
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="btn-group" role="group">
                                            <a href="admin.php?section=orders" class="btn <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                            <a href="admin.php?section=orders&status=pending" class="btn <?php echo $filter_status == 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                                            <a href="admin.php?section=orders&status=processing" class="btn <?php echo $filter_status == 'processing' ? 'btn-primary' : 'btn-outline-primary'; ?>">Processing</a>
                                            <a href="admin.php?section=orders&status=shipped" class="btn <?php echo $filter_status == 'shipped' ? 'btn-primary' : 'btn-outline-primary'; ?>">Shipped</a>
                                            <a href="admin.php?section=orders&status=completed" class="btn <?php echo $filter_status == 'completed' ? 'btn-primary' : 'btn-outline-primary'; ?>">Completed</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!$view_order): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Payment</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (mysqli_num_rows($orders_result) > 0): ?>
                                                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                                        <tr>
                                                            <td>#<?php echo $order['id']; ?></td>
                                                            <td><?php echo $order['full_name']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                            <td>
                                                                <?php
                                                                switch ($order['status']) {
                                                                    case 'pending':
                                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                                        break;
                                                                    case 'processing':
                                                                        echo '<span class="badge bg-info">Processing</span>';
                                                                        break;
                                                                    case 'shipped':
                                                                        echo '<span class="badge bg-primary">Shipped</span>';
                                                                        break;
                                                                    case 'completed':
                                                                        echo '<span class="badge bg-success">Completed</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                switch ($order['payment_status']) {
                                                                    case 'pending':
                                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                                        break;
                                                                    case 'paid':
                                                                        echo '<span class="badge bg-success">Paid</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <a href="admin.php?section=orders&view=<?php echo $order['id']; ?>" class="btn btn-sm btn-info me-1">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No orders found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <!-- Order Detail View -->
                                    <div class="mb-3">
                                        <a href="admin.php?section=orders<?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Back to Orders
                                        </a>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0">Order #<?php echo $view_order['id']; ?> Details</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong>Date:</strong> <?php echo date('F d, Y h:i A', strtotime($view_order['order_date'])); ?></p>
                                                    <p><strong>Status:</strong> 
                                                        <?php
                                                        switch ($view_order['status']) {
                                                            case 'pending':
                                                                echo '<span class="badge bg-warning">Pending</span>';
                                                                break;
                                                            case 'processing':
                                                                echo '<span class="badge bg-info">Processing</span>';
                                                                break;
                                                            case 'shipped':
                                                                echo '<span class="badge bg-primary">Shipped</span>';
                                                                break;
                                                            case 'completed':
                                                                echo '<span class="badge bg-success">Completed</span>';
                                                                break;
                                                            default:
                                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                                        }
                                                        ?>
                                                    </p>
                                                    <p><strong>Payment Status:</strong> 
                                                        <?php
                                                        switch ($view_order['payment_status']) {
                                                            case 'pending':
                                                                echo '<span class="badge bg-warning">Pending</span>';
                                                                break;
                                                            case 'paid':
                                                                echo '<span class="badge bg-success">Paid</span>';
                                                                break;
                                                            default:
                                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                                        }
                                                        ?>
                                                    </p>
                                                    <p><strong>Total Amount:</strong> $<?php echo number_format($view_order['total_amount'], 2); ?></p>
                                                    
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="order_id" value="<?php echo $view_order['id']; ?>">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="status" class="form-label">Update Status</label>
                                                                <select class="form-select" id="status" name="status">
                                                                    <option value="pending" <?php echo $view_order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="processing" <?php echo $view_order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                                    <option value="shipped" <?php echo $view_order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                                    <option value="completed" <?php echo $view_order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label for="payment_status" class="form-label">Payment Status</label>
                                                                <select class="form-select" id="payment_status" name="payment_status">
                                                                    <option value="pending" <?php echo $view_order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="paid" <?php echo $view_order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="update_order_status" class="btn btn-primary">
                                                            <i class="fas fa-save me-1"></i> Update Order
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="mb-0">Customer Information</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong>Name:</strong> <?php echo $view_order['full_name']; ?></p>
                                                    <p><strong>Email:</strong> <?php echo $view_order['email']; ?></p>
                                                    <p><strong>Phone:</strong> <?php echo $view_order['phone'] ?: 'N/A'; ?></p>
                                                    <p><strong>Shipping Address:</strong> <?php echo $view_order['shipping_address'] ?: $view_order['address']; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">Order Items</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Product</th>
                                                            <th>Image</th>
                                                            <th>Price</th>
                                                            <th>Quantity</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $order_total = 0;
                                                        while ($item = mysqli_fetch_assoc($order_items)): 
                                                            $item_total = $item['price'] * $item['quantity'];
                                                            $order_total += $item_total;
                                                        ?>
                                                            <tr>
                                                                <td><?php echo $item['name']; ?></td>
                                                                <td>
                                                                    <?php if (!empty($item['image_url'])): ?>
                                                                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                                                    <?php else: ?>
                                                                        <span class="text-muted">No image</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                                <td><?php echo $item['quantity']; ?></td>
                                                                <td>$<?php echo number_format($item_total, 2); ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="4" class="text-end">Order Total:</th>
                                                            <th>$<?php echo number_format($order_total, 2); ?></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        break;
                        
                    case 'users':
                        // Users Management
                        
                        // Search for users
                        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                        
                        $where_clause = "";
                        if (!empty($search)) {
                            $where_clause = " WHERE full_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
                        }
                        
                        $query = "SELECT * FROM users $where_clause ORDER BY id DESC";
                        $users_result = mysqli_query($conn, $query);
                        
                        // Get user details if viewing a specific user
                        $view_user = null;
                        $user_orders = null;
                        if (isset($_GET['view']) && is_numeric($_GET['view'])) {
                            $view_id = (int)$_GET['view'];
                            $view_query = "SELECT * FROM users WHERE id = $view_id";
                            $view_result = mysqli_query($conn, $view_query);
                            $view_user = mysqli_fetch_assoc($view_result);
                            
                            // Get user orders
                            $orders_query = "SELECT * FROM orders WHERE user_id = $view_id ORDER BY order_date DESC";
                            $user_orders = mysqli_query($conn, $orders_query);
                        }
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <form method="GET" action="" class="d-flex">
                                            <input type="hidden" name="section" value="users">
                                            <input type="text" name="search" class="form-control me-2" placeholder="Search users..." value="<?php echo $search; ?>">
                                            <button type="submit" class="btn btn-primary">Search</button>
                                            <?php if (!empty($search)): ?>
                                                <a href="admin.php?section=users" class="btn btn-secondary ms-2">Clear</a>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!$view_user): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Orders</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (mysqli_num_rows($users_result) > 0): ?>
                                                    <?php while ($user = mysqli_fetch_assoc($users_result)): 
                                                        // Count orders for this user
                                                        $user_id = $user['id'];
                                                        $order_count_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id";
                                                        $order_count_result = mysqli_query($conn, $order_count_query);
                                                        $order_count = mysqli_fetch_assoc($order_count_result)['count'];
                                                    ?>
                                                        <tr>
                                                            <td><?php echo $user['id']; ?></td>
                                                            <td><?php echo $user['full_name']; ?></td>
                                                            <td><?php echo $user['email']; ?></td>
                                                            <td><?php echo $user['phone'] ?: 'N/A'; ?></td>
                                                            <td>
                                                                <?php if ($order_count > 0): ?>
                                                                    <span class="badge bg-success"><?php echo $order_count; ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">0</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a href="admin.php?section=users&view=<?php echo $user['id']; ?>" class="btn btn-sm btn-info me-1">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="admin.php?section=users&delete=1&id=<?php echo $user['id']; ?>&table=users" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This will also delete all their orders.');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No users found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <!-- User Detail View -->
                                    <div class="mb-3">
                                        <a href="admin.php?section=users<?php echo !empty($search) ? '&search=' . $search : ''; ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Back to Users
                                        </a>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="mb-0">User Details</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong>ID:</strong> <?php echo $view_user['id']; ?></p>
                                                    <p><strong>Name:</strong> <?php echo $view_user['full_name']; ?></p>
                                                    <p><strong>Email:</strong> <?php echo $view_user['email']; ?></p>
                                                    <p><strong>Phone:</strong> <?php echo $view_user['phone'] ?: 'N/A'; ?></p>
                                                    <p><strong>Address:</strong> <?php echo $view_user['address'] ?: 'N/A'; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                    <h5 class="mb-0">User Activity</h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php
                                                    // Get user's cart items
                                                    $cart_query = "SELECT ci.*, p.name, p.price 
                                                                   FROM carts c 
                                                                   JOIN cart_items ci ON c.id = ci.cart_id 
                                                                   JOIN products p ON ci.product_id = p.id 
                                                                   WHERE c.user_id = $view_id";
                                                    $cart_result = mysqli_query($conn, $cart_query);
                                                    $cart_count = mysqli_num_rows($cart_result);
                                                    
                                                    // Get user's wishlist items
                                                    $wishlist_query = "SELECT w.*, p.name, p.price 
                                                                      FROM wishlist w 
                                                                      JOIN products p ON w.product_id = p.id 
                                                                      WHERE w.user_id = $view_id";
                                                    $wishlist_result = mysqli_query($conn, $wishlist_query);
                                                    $wishlist_count = mysqli_num_rows($wishlist_result);
                                                    
                                                    // Get user's order count
                                                    $orders_count = mysqli_num_rows($user_orders);
                                                    
                                                    // Get user's reviews
                                                    $reviews_query = "SELECT pr.*, p.name 
                                                                     FROM product_reviews pr 
                                                                     JOIN products p ON pr.product_id = p.id 
                                                                     WHERE pr.user_id = $view_id 
                                                                     ORDER BY pr.created_at DESC";
                                                    $reviews_result = mysqli_query($conn, $reviews_query);
                                                    $reviews_count = mysqli_num_rows($reviews_result);
                                                    ?>
                                                    
                                                    <p><strong>Orders:</strong> <?php echo $orders_count; ?></p>
                                                    <p><strong>Cart Items:</strong> <?php echo $cart_count; ?></p>
                                                    <p><strong>Wishlist Items:</strong> <?php echo $wishlist_count; ?></p>
                                                    <p><strong>Reviews:</strong> <?php echo $reviews_count; ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($orders_count > 0): ?>
                                        <div class="card mb-4">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">User Orders</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Order ID</th>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Status</th>
                                                                <th>Payment</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php while ($order = mysqli_fetch_assoc($user_orders)): ?>
                                                                <tr>
                                                                    <td>#<?php echo $order['id']; ?></td>
                                                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                                    <td>
                                                                        <?php
                                                                        switch ($order['status']) {
                                                                            case 'pending':
                                                                                echo '<span class="badge bg-warning">Pending</span>';
                                                                                break;
                                                                            case 'processing':
                                                                                echo '<span class="badge bg-info">Processing</span>';
                                                                                break;
                                                                            case 'shipped':
                                                                                echo '<span class="badge bg-primary">Shipped</span>';
                                                                                break;
                                                                            case 'completed':
                                                                                echo '<span class="badge bg-success">Completed</span>';
                                                                                break;
                                                                            default:
                                                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        switch ($order['payment_status']) {
                                                                            case 'pending':
                                                                                echo '<span class="badge bg-warning">Pending</span>';
                                                                                break;
                                                                            case 'paid':
                                                                                echo '<span class="badge bg-success">Paid</span>';
                                                                                break;
                                                                            default:
                                                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td>
                                                                        <a href="admin.php?section=orders&view=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                                            <i class="fas fa-eye"></i> View
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($reviews_count > 0): ?>
                                        <div class="card mb-4">
                                            <div class="card-header bg-warning text-dark">
                                                <h5 class="mb-0">User Reviews</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                                                    <div class="border p-3 mb-3">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h5 class="m-0"><?php echo $review['name']; ?></h5>
                                                            <div>
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <p class="text-muted mb-2">
                                                            <small>Posted on <?php echo date('F d, Y', strtotime($review['created_at'])); ?></small>
                                                        </p>
                                                        <p class="mb-0"><?php echo $review['review_text']; ?></p>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        break;
                        
                    case 'contacts':
                        // Contact Messages
                        
                        // Filter by status
                        $filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
                        
                        $where_clause = "";
                        if (!empty($filter_status)) {
                            $where_clause = " WHERE status = '$filter_status'";
                        }
                        
                        $query = "SELECT * FROM contacts $where_clause ORDER BY created_at DESC";
                        $contacts_result = mysqli_query($conn, $query);
                        
                        // View specific message
                        $view_message = null;
                        if (isset($_GET['view']) && is_numeric($_GET['view'])) {
                            $view_id = (int)$_GET['view'];
                            $view_query = "SELECT * FROM contacts WHERE id = $view_id";
                            $view_result = mysqli_query($conn, $view_query);
                            $view_message = mysqli_fetch_assoc($view_result);
                            
                            // Mark message as read if it was unread
                            if ($view_message['status'] == 'unread') {
                                $update_query = "UPDATE contacts SET status = 'read', updated_at = NOW() WHERE id = $view_id";
                                mysqli_query($conn, $update_query);
                                $view_message['status'] = 'read';
                            }
                        }
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="btn-group" role="group">
                                            <a href="admin.php?section=contacts" class="btn <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                                            <a href="admin.php?section=contacts&status=unread" class="btn <?php echo $filter_status == 'unread' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                                Unread
                                                <?php if ($unread_messages > 0): ?>
                                                    <span class="badge bg-danger ms-1"><?php echo $unread_messages; ?></span>
                                                <?php endif; ?>
                                            </a>
                                            <a href="admin.php?section=contacts&status=read" class="btn <?php echo $filter_status == 'read' ? 'btn-primary' : 'btn-outline-primary'; ?>">Read</a>
                                            <a href="admin.php?section=contacts&status=responded" class="btn <?php echo $filter_status == 'responded' ? 'btn-primary' : 'btn-outline-primary'; ?>">Responded</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!$view_message): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Subject</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (mysqli_num_rows($contacts_result) > 0): ?>
                                                    <?php while ($message = mysqli_fetch_assoc($contacts_result)): ?>
                                                        <tr class="<?php echo $message['status'] == 'unread' ? 'table-warning' : ''; ?>">
                                                            <td><?php echo $message['id']; ?></td>
                                                            <td><?php echo $message['name']; ?></td>
                                                            <td><?php echo $message['email']; ?></td>
                                                            <td><?php echo $message['subject']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                                            <td>
                                                                <?php
                                                                switch ($message['status']) {
                                                                    case 'unread':
                                                                        echo '<span class="badge bg-warning">Unread</span>';
                                                                        break;
                                                                    case 'read':
                                                                        echo '<span class="badge bg-info">Read</span>';
                                                                        break;
                                                                    case 'responded':
                                                                        echo '<span class="badge bg-success">Responded</span>';
                                                                        break;
                                                                    default:
                                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td>
                                                                <a href="admin.php?section=contacts&view=<?php echo $message['id']; ?>" class="btn btn-sm btn-info me-1">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="admin.php?section=contacts&delete=1&id=<?php echo $message['id']; ?>&table=contacts" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">No messages found.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <!-- View Message -->
                                    <div class="mb-3">
                                        <a href="admin.php?section=contacts<?php echo !empty($filter_status) ? '&status=' . $filter_status : ''; ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Back to Messages
                                        </a>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Message Details</h5>
                                            <div>
                                                <?php
                                                switch ($view_message['status']) {
                                                    case 'unread':
                                                        echo '<span class="badge bg-warning">Unread</span>';
                                                        break;
                                                    case 'read':
                                                        echo '<span class="badge bg-info">Read</span>';
                                                        break;
                                                    case 'responded':
                                                        echo '<span class="badge bg-success">Responded</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3 row">
                                                <label class="col-md-2 fw-bold">From:</label>
                                                <div class="col-md-10"><?php echo $view_message['name']; ?> (<?php echo $view_message['email']; ?>)</div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-2 fw-bold">Subject:</label>
                                                <div class="col-md-10"><?php echo $view_message['subject']; ?></div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-2 fw-bold">Date:</label>
                                                <div class="col-md-10"><?php echo date('F d, Y H:i:s', strtotime($view_message['created_at'])); ?></div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-2 fw-bold">Message:</label>
                                                <div class="col-md-10">
                                                    <div class="p-3 border bg-light">
                                                        <?php echo nl2br(htmlspecialchars($view_message['message'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <a href="mailto:<?php echo $view_message['email']; ?>?subject=Re: <?php echo urlencode($view_message['subject']); ?>" class="btn btn-primary me-2">
                                                    <i class="fas fa-reply me-1"></i> Reply via Email
                                                </a>
                                                <a href="admin.php?section=contacts&mark_as=responded&id=<?php echo $view_message['id']; ?>" class="btn btn-success me-2">
                                                    <i class="fas fa-check me-1"></i> Mark as Responded
                                                </a>
                                                <a href="admin.php?section=contacts&delete=1&id=<?php echo $view_message['id']; ?>&table=contacts" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        break;
case 'reviews':
    // Reviews Management
    
    // Filter and search
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $filter_rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
    $filter_product = isset($_GET['product']) ? (int)$_GET['product'] : 0;
    
    $where_clause = "";
    $conditions = [];
    
    if (!empty($search)) {
        $conditions[] = "(pr.review_text LIKE '%$search%' OR p.name LIKE '%$search%' OR u.full_name LIKE '%$search%')";
    }
    
    if ($filter_rating > 0) {
        $conditions[] = "pr.rating = $filter_rating";
    }
    
    if ($filter_product > 0) {
        $conditions[] = "pr.product_id = $filter_product";
    }
    
    if (!empty($conditions)) {
        $where_clause = " WHERE " . implode(" AND ", $conditions);
    }
    
    $query = "SELECT pr.*, p.name as product_name, u.full_name as user_name, u.email as user_email 
              FROM product_reviews pr 
              JOIN products p ON pr.product_id = p.id 
              JOIN users u ON pr.user_id = u.id
              $where_clause
              ORDER BY pr.created_at DESC";
    $reviews_result = mysqli_query($conn, $query);
    
    // Get products for filter dropdown
    $products_query = "SELECT id, name FROM products ORDER BY name";
    $products_result = mysqli_query($conn, $products_query);
    $products = [];
    while ($prod = mysqli_fetch_assoc($products_result)) {
        $products[] = $prod;
    }
    
    // View specific review
    $view_review = null;
    if (isset($_GET['view']) && is_numeric($_GET['view'])) {
        $view_id = (int)$_GET['view'];
        $view_query = "SELECT pr.*, p.name as product_name, p.image_url, u.full_name as user_name, u.email as user_email 
                       FROM product_reviews pr 
                       JOIN products p ON pr.product_id = p.id 
                       JOIN users u ON pr.user_id = u.id 
                       WHERE pr.id = $view_id";
        $view_result = mysqli_query($conn, $view_query);
        $view_review = mysqli_fetch_assoc($view_result);
    }
    
    ?>
    <div class="card mb-4">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <form method="GET" action="" class="d-flex flex-wrap gap-2">
                        <input type="hidden" name="section" value="reviews">
                        <input type="text" name="search" class="form-control" placeholder="Search reviews..." value="<?php echo $search; ?>" style="max-width: 250px;">
                        <select name="rating" class="form-select" style="max-width: 150px;">
                            <option value="0">All Ratings</option>
                            <option value="5" <?php echo $filter_rating == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $filter_rating == 4 ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo $filter_rating == 3 ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo $filter_rating == 2 ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo $filter_rating == 1 ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                        <select name="product" class="form-select" style="max-width: 200px;">
                            <option value="0">All Products</option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?php echo $prod['id']; ?>" <?php echo $filter_product == $prod['id'] ? 'selected' : ''; ?>>
                                    <?php echo $prod['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if (!empty($search) || $filter_rating > 0 || $filter_product > 0): ?>
                            <a href="admin.php?section=reviews" class="btn btn-secondary">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (!$view_review): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                                    <tr>
                                        <td><?php echo $review['id']; ?></td>
                                        <td><?php echo $review['product_name']; ?></td>
                                        <td><?php echo $review['user_name']; ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-1">(<?php echo $review['rating']; ?>)</span>
                                        </td>
                                        <td><?php echo substr($review['review_text'], 0, 100) . (strlen($review['review_text']) > 100 ? '...' : ''); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                        <td>
                                            <a href="admin.php?section=reviews&view=<?php echo $review['id']; ?>" class="btn btn-sm btn-info me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="admin.php?section=reviews&delete=1&id=<?php echo $review['id']; ?>&table=reviews" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No reviews found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- View Review Details -->
                <div class="mb-3">
                    <a href="admin.php?section=reviews" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reviews
                    </a>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Review Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Product:</strong> <?php echo $view_review['product_name']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Customer:</strong> <?php echo $view_review['user_name']; ?> (<?php echo $view_review['user_email']; ?>)
                                </div>
                                <div class="mb-3">
                                    <strong>Rating:</strong>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $view_review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2"><?php echo $view_review['rating']; ?> out of 5</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Date:</strong> <?php echo date('F d, Y H:i:s', strtotime($view_review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($view_review['image_url'])): ?>
                                    <div class="mb-3">
                                        <strong>Product Image:</strong><br>
                                        <img src="<?php echo $view_review['image_url']; ?>" alt="<?php echo $view_review['product_name']; ?>" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <a href="admin.php?section=products&edit=<?php echo $view_review['product_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i> Edit Product
                                    </a>
                                    <a href="admin.php?section=users&view=<?php echo $view_review['user_id']; ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-user me-1"></i> View User
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <strong>Review Text:</strong>
                            <div class="p-3 border bg-light mt-2">
                                <?php echo nl2br(htmlspecialchars($view_review['review_text'])); ?>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="admin.php?section=reviews&delete=1&id=<?php echo $view_review['id']; ?>&table=reviews" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this review?');">
                                <i class="fas fa-trash me-1"></i> Delete Review
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    break;
                        
                    case 'admins':
                        // Admins Management
                        
                        // Get all admins
                        $query = "SELECT * FROM admins ORDER BY id";
                        $admins_result = mysqli_query($conn, $query);
                        
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="mb-0">Admin Accounts</h5>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <a href="register.php" class="btn btn-success">
                                            <i class="fas fa-plus me-1"></i> Add New Admin
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Last Login</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (mysqli_num_rows($admins_result) > 0): ?>
                                                <?php while ($admin_user = mysqli_fetch_assoc($admins_result)): ?>
                                                    <tr>
                                                        <td><?php echo $admin_user['id']; ?></td>
                                                        <td><?php echo $admin_user['username']; ?></td>
                                                        <td><?php echo $admin_user['email']; ?></td>
                                                        <td><?php echo ucfirst($admin_user['role']); ?></td>
                                                        <td>
                                                            <?php 
                                                            echo $admin_user['last_login'] 
                                                                ? date('M d, Y H:i', strtotime($admin_user['last_login'])) 
                                                                : 'Never'; 
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($admin_user['id'] != $admin_id): ?>
                                                                <a href="admin.php?section=admins&delete=1&id=<?php echo $admin_user['id']; ?>&table=admins" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this admin?');">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete your own account">
                                                                    <i class="fas fa-user-shield"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No admins found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php
                        break;
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <script>
        // Auto-close alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>