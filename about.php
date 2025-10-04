<?php
require 'helpers.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>About Us - DreamHomes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="fas fa-home me-2"></i>DreamHomes
    </a>
    
    <!-- Mobile toggler button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Navigation Links -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a href="index.php" class="nav-link">Home</a>
        </li>
        <li class="nav-item">
          <a href="about.php" class="nav-link active">About</a>
        </li>
        <li class="nav-item">
          <a href="contact.php" class="nav-link">Contact Us</a>
        </li>
        <?php if (is_admin()): ?>
  <li class="nav-item">
    <a href="admin.php" class="nav-link">Admin Dashboard</a>
  </li>
<?php endif; ?>
      </ul>
      
      <!-- User actions -->
      <div class="d-flex align-items-center">
        <?php if (is_logged_in()): ?>
          <!-- User Profile Dropdown -->
          <div class="dropdown ms-3">
            <button class="btn p-0 d-flex align-items-center text-decoration-none dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
              </div>
              <span class="d-none d-md-inline"><?= e($_SESSION['user_name'] ?? 'User') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="favorites.php"><i class="fas fa-heart me-2"></i>My Favorites</a></li>
              <?php if (is_admin()): ?>
                <li><a class="dropdown-item" href="add_house.php"><i class="fas fa-plus me-2"></i>Add Listing</a></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <div class="d-flex">
            <a class="btn btn-primary btn-sm me-2" href="login.php">
              <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
            <a class="btn btn-outline btn-sm" href="register.php">
              <i class="fas fa-user-plus me-1"></i>Signup
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <h1 class="text-center mb-4">About DreamHomes</h1>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Our Story</h3>
          <p class="card-text">
            Founded in 2015, DreamHomes began with a simple mission: to make finding your perfect home easier and more enjoyable. 
            What started as a small local service has grown into a trusted platform connecting thousands of buyers and sellers across the country.
          </p>
          <p class="card-text">
            Our team of dedicated real estate professionals works tirelessly to ensure that every listing on our platform meets 
            the highest standards of quality and accuracy.
          </p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Our Mission</h3>
          <p class="card-text">
            At DreamHomes, we believe that everyone deserves to find a place they can truly call home. Our mission is to simplify 
            the property search process through innovative technology, personalized service, and comprehensive listings.
          </p>
          <p class="card-text">
            We're committed to transparency, integrity, and excellence in everything we do, from the way we present properties 
            to how we support our clients throughout their real estate journey.
          </p>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-body">
          <h3 class="card-title">Why Choose Us?</h3>
          <div class="row mt-4">
            <div class="col-md-6 mb-3">
              <div class="d-flex">
                <div class="me-3">
                  <i class="fas fa-check-circle text-primary fa-2x"></i>
                </div>
                <div>
                  <h5>Verified Listings</h5>
                  <p class="text-muted">Every property is verified by our team to ensure accuracy and quality.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex">
                <div class="me-3">
                  <i class="fas fa-check-circle text-primary fa-2x"></i>
                </div>
                <div>
                  <h5>Expert Support</h5>
                  <p class="text-muted">Our team of real estate experts is always ready to assist you.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex">
                <div class="me-3">
                  <i class="fas fa-check-circle text-primary fa-2x"></i>
                </div>
                <div>
                  <h5>Modern Platform</h5>
                  <p class="text-muted">Easy-to-use website with advanced search and filtering options.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="d-flex">
                <div class="me-3">
                  <i class="fas fa-check-circle text-primary fa-2x"></i>
                </div>
                <div>
                  <h5>Secure Transactions</h5>
                  <p class="text-muted">Your privacy and security are our top priorities.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-body text-center">
          <h3 class="card-title">Join Our Community</h3>
          <p class="card-text">
            Thousands of satisfied clients have found their dream homes through our platform. 
            Whether you're buying, selling, or just exploring, we're here to help you every step of the way.
          </p>
          <a href="register.php" class="btn btn-primary mt-3">
            <i class="fas fa-user-plus me-1"></i>Create Your Account
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="bg-dark text-white py-5 mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h5 class="navbar-brand text-white">DreamHomes</h5>
        <p>Find your perfect home from our curated collection of properties.</p>
      </div>
      <div class="col-md-2 mb-4">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="index.php" class="text-white">Home</a></li>
          <li><a href="about.php" class="text-white">About Us</a></li>
          <li><a href="contact.php" class="text-white">Contact</a></li>
        </ul>
      </div>
      <div class="col-md-3 mb-4">
        <h5>Legal</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white">Privacy Policy</a></li>
          <li><a href="#" class="text-white">Terms of Service</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <h5>Connect With Us</h5>
        <div class="d-flex gap-3 mt-3">
          <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
          <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
          <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
          <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
        </div>
      </div>
    </div>
    <hr class="my-4">
    <p class="text-center mb-0">&copy; <?= date('Y') ?> DreamHomes. All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>