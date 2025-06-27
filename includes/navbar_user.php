<?php
// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Rasha Camera Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="user.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Shop</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php">My Wishlist</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ai_chatboot.php">AI Chatboot</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php" style="padding: 8px 16px;">Contact</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Cart
                        <?php
                        $cart_count = 0;
                        if (isset($_SESSION['cart'])) {
                            $cart_count = count($_SESSION['cart']);
                        }
                        ?>
                        <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="user.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="profile.php">Edit Profile</a></li>
                        <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>