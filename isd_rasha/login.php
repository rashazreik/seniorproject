<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .login-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1616088886430-caaae30e3dcb');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            color: white;
            margin-bottom: 40px;
        }
        #reset-password-form {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        #set-new-password-form {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Login Banner -->
<div class="login-banner text-center">
    <div class="container">
        <h1 class="display-4">User Login</h1>
        <p class="lead">Access your account to manage orders and more</p>
    </div>
</div>

<div class="container login-container">
    <?php
    // Include database connection
    require_once 'includes/config.php';
    
    // Initialize variables
    $email = $password = "";
    $email_err = $password_err = $login_err = "";
    $reset_email = $security_answer = $new_password = $confirm_password = "";
    $reset_email_err = $security_answer_err = $new_password_err = $confirm_password_err = "";
    $reset_success = $reset_error = "";
    
    // Array of security questions
    $security_questions = array(
        "What was your childhood nickname?",
        "In what city were you born?",
        "What is your mother's maiden name?",
        "What was the name of your first pet?",
        "What was the name of your elementary school?"
    );
    
    // Select a random security question
    $random_question_index = rand(0, count($security_questions) - 1);
    $selected_question = $security_questions[$random_question_index];
    
    // Process login form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
        
        // Check if email key exists in $_POST before using it
        if (isset($_POST["email"])) {
            // Validate email
            if (empty(trim($_POST["email"]))) {
                $email_err = "Please enter your email.";
            } else {
                $email = trim($_POST["email"]);
            }
        }
        
        // Check if password key exists in $_POST before using it
        if (isset($_POST["password"])) {
            // Validate password
            if (empty(trim($_POST["password"]))) {
                $password_err = "Please enter your password.";
            } else {
                $password = trim($_POST["password"]);
            }
        }
        
        // Check input errors before authenticating
        if (isset($email) && isset($password) && empty($email_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = $email;
                
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if email exists, if yes then verify password
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $full_name, $email, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($password, $hashed_password)) {
                                // Password is correct, start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["full_name"] = $full_name;
                                $_SESSION["email"] = $email;
                                
                                // Redirect user to welcome page
                                header("location: user.php");
                            } else {
                                // Password is not valid
                                $login_err = "Invalid email or password.";
                            }
                        }
                    } else {
                        // Email doesn't exist
                        $login_err = "Invalid email or password.";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // Process reset password form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reset_submit"])) {
        // Validate email
        if (empty(trim($_POST["reset_email"]))) {
            $reset_email_err = "Please enter your email.";
        } else {
            $reset_email = trim($_POST["reset_email"]);
        }
        
        // Validate security answer
        if (empty(trim($_POST["security_answer"]))) {
            $security_answer_err = "Please answer the security question.";
        } else {
            $security_answer = trim($_POST["security_answer"]);
        }
        
        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $new_password_err = "Password must have at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($new_password_err) && ($new_password != $confirm_password)) {
                $confirm_password_err = "Passwords did not match.";
            }
        }
        
        // Check input errors before updating the database
        if (empty($reset_email_err) && empty($security_answer_err) && empty($new_password_err) && empty($confirm_password_err)) {
            // Prepare a select statement to check if the email exists and validate security answer
            $sql = "SELECT id, email, security_answer FROM users WHERE email = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = $reset_email;
                
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if email exists
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $email, $stored_security_answer);
                        if (mysqli_stmt_fetch($stmt)) {
                            // Check if security answer matches
                            if ($security_answer === $stored_security_answer) {
                                // Prepare an update statement
                                $sql = "UPDATE users SET password = ? WHERE id = ?";
                                
                                if ($update_stmt = mysqli_prepare($conn, $sql)) {
                                    // Bind variables to the prepared statement as parameters
                                    mysqli_stmt_bind_param($update_stmt, "si", $param_password, $param_id);
                                    
                                    // Set parameters
                                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                    $param_id = $id;
                                    
                                    // Attempt to execute the prepared statement
                                    if (mysqli_stmt_execute($update_stmt)) {
                                        // Password updated successfully
                                        $reset_success = "Your password has been reset successfully. You can now login with your new password.";
                                        
                                        // Clear form data
                                        $reset_email = $security_answer = $new_password = $confirm_password = "";
                                        
                                        // Show login form again
                                        echo "<script>
                                            $(document).ready(function(){
                                                $('#reset-password-form').hide();
                                                $('#login-form').show();
                                            });
                                        </script>";
                                    } else {
                                        $reset_error = "Oops! Something went wrong. Please try again later.";
                                    }
                                    
                                    // Close statement
                                    mysqli_stmt_close($update_stmt);
                                }
                            } else {
                                $security_answer_err = "The security answer is incorrect.";
                            }
                        }
                    } else {
                        $reset_email_err = "No account found with that email.";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // Close connection
    mysqli_close($conn);
    ?>

    <div class="form-card bg-white">
        <h2 class="text-center mb-4">Login to Your Account</h2>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }
        if(!empty($reset_success)){
            echo '<div class="alert alert-success">' . $reset_success . '</div>';
        }
        if(!empty($reset_error)){
            echo '<div class="alert alert-danger">' . $reset_error . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="login-form">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" placeholder="Example@gmail.com" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>    
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" placeholder="Your password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="button" id="forgot-password-btn" class="btn btn-link p-0">Forgot Password?</button>
            </div>
            <div class="d-grid">
                <button type="submit" name="login" class="btn btn-primary">Login</button>
            </div>
        </form>
        
        <!-- Reset Password Form (Initially Hidden) -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="reset-password-form">
            <h4 class="mb-3">Reset Your Password</h4>
            <p>Please fill out this form to reset your password.</p>
            
            <div class="mb-3">
                <label for="reset_email" class="form-label">Email</label>
                <input type="email" name="reset_email" class="form-control <?php echo (!empty($reset_email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $reset_email; ?>" placeholder="Enter your email">
                <span class="invalid-feedback"><?php echo $reset_email_err; ?></span>
            </div>
            
            <div class="mb-3">
                <label for="security_question" class="form-label">Security Question</label>
                <input type="text" class="form-control" value="<?php echo $selected_question; ?>" readonly>
                <input type="hidden" name="security_question_index" value="<?php echo $random_question_index; ?>">
            </div>
            
            <div class="mb-3">
                <label for="security_answer" class="form-label">Your Answer</label>
                <input type="text" name="security_answer" class="form-control <?php echo (!empty($security_answer_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $security_answer; ?>">
                <span class="invalid-feedback"><?php echo $security_answer_err; ?></span>
            </div>
            
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" name="reset_submit" class="btn btn-primary">Reset Password</button>
                <button type="button" id="back-to-login-btn" class="btn btn-outline-secondary">Back to Login</button>
            </div>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center">
            <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
  
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Custom JavaScript for the forgot password functionality -->
<script>
    $(document).ready(function(){
        // Show reset password form when "Forgot Password" is clicked
        $("#forgot-password-btn").click(function(){
            $("#login-form").hide();
            $("#reset-password-form").show();
        });
        
        // Show login form when "Back to Login" is clicked
        $("#back-to-login-btn").click(function(){
            $("#reset-password-form").hide();
            $("#login-form").show();
        });
    });
</script>

</body>
</html>