<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'includes/config.php';

// Initialize variables
$full_name = $email = $phone = $address = "";
$full_name_err = $email_err = "";
$success_message = "";

// Get user data
$user_id = $_SESSION["id"];
$sql = "SELECT full_name, email, phone, address FROM users WHERE id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $full_name, $email, $phone, $address);
            mysqli_stmt_fetch($stmt);
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate full name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name";
    } else {
        $full_name = trim($_POST["full_name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email";
    } else {
        // Check if email is already taken by another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_email, $param_user_id);
            
            $param_email = trim($_POST["email"]);
            $param_user_id = $user_id;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // No validation for optional fields
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    
    // Check if there are no errors
    if (empty($full_name_err) && empty($email_err)) {
        // Update user data
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $param_full_name, $param_email, $param_phone, $param_address, $param_user_id);
            
            $param_full_name = $full_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_address = $address;
            $param_user_id = $user_id;
            
            if (mysqli_stmt_execute($stmt)) {
                // Update session variables
                $_SESSION["full_name"] = $full_name;
                $_SESSION["email"] = $email;
                
                $success_message = "Profile updated successfully!";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Include User Navbar -->
<?php include 'includes/navbar_user.php'; ?>

<!-- Edit Profile Section -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Profile</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($full_name); ?>">
                            <div class="invalid-feedback"><?php echo $full_name_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone (Optional)</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address (Optional)</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">Update Profile</button>
                            <a href="user.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <a href="change_password.php" class="text-decoration-none">Change Password</a>
            </div>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>