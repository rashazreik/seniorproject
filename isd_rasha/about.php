<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Camera Shop</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom inline styles -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1542038784456-1ea8e935640e?q=80&w=1000');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }
        
        .mission-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        
        .team-section {
            padding: 80px 0;
        }
        
        .team-member-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<!-- Include Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">About Camera Shop</h1>
        <p class="lead">Your Trusted Partner in Photography Since 2010</p>
    </div>
</section>

<!-- Our Story Section -->
<section class="mission-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-4">Our Story</h2>
                <p>Founded in 2010, Camera Shop started as a small photography equipment store in the heart of Beirut. Our founder, a passionate photographer, noticed a gap in the market for high-quality photography equipment paired with expert advice.</p>
                <p>Over the years, we've grown from a single location to become Lebanon's premier destination for photography enthusiasts and professionals alike. Our growth has been fueled by a commitment to quality, competitive pricing, and unparalleled customer service.</p>
                <p>Today, we proudly serve thousands of customers across the country, helping them capture life's most precious moments with the perfect equipment for their needs and skill level.</p>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1534131707746-25d604851a1f" class="img-fluid rounded" alt="Our Store">
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <h2 class="text-center mb-5">Meet Our Team</h2>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d" class="mb-3 team-member-img" alt="Team Member">
                <h4>Ahmed Khalil</h4>
                <p class="text-muted">Founder & CEO</p>
                <p>A photography enthusiast with over 20 years of experience. Ahmed's vision drives our company's mission to provide quality equipment to photographers of all levels.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2" class="mb-3 team-member-img" alt="Team Member">
                <h4>Sarah Ibrahim</h4>
                <p class="text-muted">Head of Customer Service</p>
                <p>With her extensive knowledge of photography equipment and commitment to customer satisfaction, Sarah ensures every customer receives the guidance they need.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a" class="mb-3 team-member-img" alt="Team Member">
                <h4>Karim Sayegh</h4>
                <p class="text-muted">Technical Expert</p>
                <p>A certified camera technician with experience working with major brands. Karim leads our repair and maintenance department, ensuring your equipment performs at its best.</p>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="about.php" class="btn btn-primary">Get in Touch With Our Team</a>
        </div>
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