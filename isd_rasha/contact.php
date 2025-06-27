<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .contact-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1542567455-cd733f23fbb1');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            color: white;
            margin-bottom: 40px;
        }
        .info-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 25px;
            height: 100%;
        }
        .info-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<!-- Include Navbar Based on Login Status -->
<?php 
session_start(); // Start the session if not already started

// Debug: Check what session variables exist
// Remove these debug lines after testing
echo "<!-- DEBUG: Session variables: " . print_r($_SESSION, true) . " -->";

// Check if user is logged in - adjust these variable names based on your login system
if(isset($_SESSION['user_id']) || isset($_SESSION['logged_in']) || isset($_SESSION['username']) || isset($_SESSION['email'])) {
    // User is logged in, include user navbar
    echo "<!-- DEBUG: User is logged in, loading navbar_user.php -->";
    include 'includes/navbar_user.php';
} else {
    // User is not logged in, include regular navbar
    echo "<!-- DEBUG: User is NOT logged in, loading navbar.php -->";
    include 'includes/navbar.php';
}
?>

<!-- Contact Banner -->
<div class="contact-banner text-center">
    <div class="container">
        <h1 class="display-4">Contact Us</h1>
        <p class="lead">We'd love to hear from you</p>
    </div>
</div>

<div class="container contact-container">
    <?php
    // Include database connection
    require_once 'includes/config.php';
    
    // Define variables and initialize with empty values
    $name = $email = $subject = $message = "";
    $name_err = $email_err = $subject_err = $message_err = "";
    $success_message = $error_message = "";
    
    // Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST"){
    
        // Validate name
        if(empty(trim($_POST["name"]))){
            $name_err = "Please enter your name.";
        } else{
            $name = trim($_POST["name"]);
        }
        
        // Validate email
        if(empty(trim($_POST["email"]))){
            $email_err = "Please enter your email.";
        } else{
            $email = trim($_POST["email"]);
            // Check if email format is valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email_err = "Please enter a valid email format.";
            }
        }
        
        // Validate subject
        if(empty(trim($_POST["subject"]))){
            $subject_err = "Please enter a subject.";
        } else{
            $subject = trim($_POST["subject"]);
        }
        
        // Validate message
        if(empty(trim($_POST["message"]))){
            $message_err = "Please enter your message.";
        } else{
            $message = trim($_POST["message"]);
        }
        
        // Check input errors before inserting in database
        if(empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)){
            
            // Prepare an insert statement
            $sql = "INSERT INTO contacts (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
             
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_email, $param_subject, $param_message);
                
                // Set parameters
                $param_name = $name;
                $param_email = $email;
                $param_subject = $subject;
                $param_message = $message;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Success message
                    $success_message = "Thank you for contacting us. We will get back to you soon!";
                    
                    // Clear form data
                    $name = $email = $subject = $message = "";
                } else{
                    $error_message = "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Close connection
        mysqli_close($conn);
    }
    ?>

    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="info-card bg-white">
                <div class="text-center">
                    <i class="bi bi-geo-alt info-icon"></i>
                    <h4>Visit Us</h4>
                    <p class="mb-0">Hamra Street</p>
                    <p>Beirut, Lebanon</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="info-card bg-white">
                <div class="text-center">
                    <i class="bi bi-envelope info-icon"></i>
                    <h4>Email Us</h4>
                    <p class="mb-0">info@camerashop.com</p>
                    <p>support@camerashop.com</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="info-card bg-white">
                <div class="text-center">
                    <i class="bi bi-telephone info-icon"></i>
                    <h4>Call Us</h4>
                    <p class="mb-0">+961 1 123 456</p>
                    <p>+961 3 789 012</p>
                </div>
            </div>
        </div>
    </div>

    <div class="form-card bg-white">
        <h2 class="text-center mb-4">Send Us a Message</h2>
        
        <?php 
        if(!empty($success_message)){
            echo '<div class="alert alert-success">' . $success_message . '</div>';
        }
        if(!empty($error_message)){
            echo '<div class="alert alert-danger">' . $error_message . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>" placeholder="John Doe">
                    <span class="invalid-feedback"><?php echo $name_err; ?></span>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Your Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="john@example.com">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control <?php echo (!empty($subject_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $subject; ?>" placeholder="How can we help you?">
                <span class="invalid-feedback"><?php echo $subject_err; ?></span>
            </div>
            
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea name="message" class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>" rows="5" placeholder="Your message here..."><?php echo $message; ?></textarea>
                <span class="invalid-feedback"><?php echo $message_err; ?></span>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Send Message</button>
            </div>
        </form>
    </div>

    <div class="mt-5">
        <div style="width: 100%">
            <iframe width="100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=beirut+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</body>
</html>