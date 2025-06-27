<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .register-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1542038784456-1ea8e935640e');
            background-size: cover;
            background-position: center;
            padding: 80px 0;
            color: white;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Register Banner -->
<div class="register-banner text-center">
    <div class="container">
        <h1 class="display-4">Create an Account</h1>
        <p class="lead">Join our community of photography enthusiasts</p>
    </div>
</div>

<div class="container register-container">
    <?php
    // Include database connection
    require_once 'includes/config.php';
    
    // Security questions array
    $security_questions = array(
        1 => "What was your childhood nickname?",
        2 => "In what city were you born?",
        3 => "What is your mother's maiden name?",
        4 => "What was the name of your first pet?",
        5 => "What was the name of your elementary school?"
    );
    
    // Define variables and initialize with empty values
    $full_name = $email = $password = $confirm_password = $phone = $address = "";
    $security_question_id = $security_answer = "";
    $full_name_err = $email_err = $password_err = $confirm_password_err = $security_question_err = $security_answer_err = "";
    
    // Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST"){
    
        // Validate full name
        if(empty(trim($_POST["full_name"]))){
            $full_name_err = "Please enter your full name.";
        } else{
            $full_name = trim($_POST["full_name"]);
        }
        
        // Validate email
        if(empty(trim($_POST["email"]))){
            $email_err = "Please enter your email.";
        } else{
            // Prepare a select statement
            $sql = "SELECT id FROM users WHERE email = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                
                // Set parameters
                $param_email = trim($_POST["email"]);
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $email_err = "This email is already taken.";
                    } else{
                        $email = trim($_POST["email"]);
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate password
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter a password.";     
        } elseif(strlen(trim($_POST["password"])) < 6){
            $password_err = "Password must have at least 6 characters.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if(empty(trim($_POST["confirm_password"]))){
            $confirm_password_err = "Please confirm password.";     
        } else{
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password)){
                $confirm_password_err = "Password did not match.";
            }
        }
        
        // Validate security question
        if(empty($_POST["security_question_id"])){
            $security_question_err = "Please select a security question.";
        } else {
            $security_question_id = $_POST["security_question_id"];
        }
        
        // Validate security answer
        if(empty(trim($_POST["security_answer"]))){
            $security_answer_err = "Please provide an answer to the security question.";
        } else {
            $security_answer = trim($_POST["security_answer"]);
        }
        
        // Get additional fields
        $phone = trim($_POST["phone"] ?? "");
        $address = trim($_POST["address"] ?? "");
        
        // Check input errors before inserting in database
        if(empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) 
           && empty($security_question_err) && empty($security_answer_err)){
            
            // Prepare an insert statement
            $sql = "INSERT INTO users (full_name, email, password, phone, address, security_question_id, security_answer) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
             
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "sssssss", $param_full_name, $param_email, $param_password, 
                                      $param_phone, $param_address, $param_security_question_id, $param_security_answer);
                
                // Set parameters
                $param_full_name = $full_name;
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                $param_phone = $phone;
                $param_address = $address;
                $param_security_question_id = $security_question_id;
                $param_security_answer = $security_answer;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Start session
                    session_start();
                    
                    // Get the ID of the newly registered user
                    $user_id = mysqli_insert_id($conn);
                    
                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $user_id;
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["email"] = $email;
                    
                    // Redirect to user dashboard
                    header("location: user.php");
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Close connection
        mysqli_close($conn);
    }
    ?>

    <div class="form-card bg-white">
        <h2 class="text-center mb-4">Create Your Account</h2>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" placeholder="Full name" class="form-control <?php echo (!empty($full_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $full_name; ?>">
                    <span class="invalid-feedback"><?php echo $full_name_err; ?></span>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" placeholder="Example@gmail.com" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" placeholder="Your password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
            </div>
            
            <!-- Security Question Section -->
            <div class="row mb-3">
                <div class="col-md-12 mb-2">
                    <h5>Password Recovery Information</h5>
                    <p class="small text-muted">This information will be used to verify your identity if you need to reset your password.</p>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="security_question_id" class="form-label">Security Question</label>
                    <select name="security_question_id" class="form-select <?php echo (!empty($security_question_err)) ? 'is-invalid' : ''; ?>">
                        <option value="">Select a security question</option>
                        <?php
                        foreach($security_questions as $id => $question){
                            echo '<option value="' . $id . '"' . ($security_question_id == $id ? ' selected' : '') . '>' . $question . '</option>';
                        }
                        ?>
                    </select>
                    <span class="invalid-feedback"><?php echo $security_question_err; ?></span>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="security_answer" class="form-label">Your Answer</label>
                    <input type="text" name="security_answer" placeholder="Answer to your security question" class="form-control <?php echo (!empty($security_answer_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $security_answer; ?>">
                    <span class="invalid-feedback"><?php echo $security_answer_err; ?></span>
                    <div class="form-text">Remember this answer exactly as you type it. It will be needed if you forget your password.</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number (Optional)</label>
                <input type="tel" name="phone" placeholder="Your number" class="form-control" value="<?php echo $phone; ?>">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label">Address (Optional)</label>
                <textarea name="address" placeholder="Address" class="form-control" rows="2"><?php echo $address; ?></textarea>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a> and <a href="#" class="text-decoration-none">Privacy Policy</a></label>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center">
            <p>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
        </div>
    </div>
</div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>