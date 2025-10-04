<?php
require 'db.php';
require 'helpers.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's favorite houses
$stmt = $pdo->prepare("
    SELECT h.* 
    FROM houses h 
    INNER JOIN favorites f ON h.id = f.house_id 
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user_id]);
$favorite_houses = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Favorites - DreamHomes</title>
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
          <a href="about.php" class="nav-link">About</a>
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
  <h1 class="text-center mb-4">My Favorite Properties</h1>
  
  <?php if (empty($favorite_houses)): ?>
    <div class="text-center py-5">
      <i class="fas fa-heart fa-3x text-muted mb-3"></i>
      <h3>No favorites yet</h3>
      <p class="text-muted">Start browsing properties and add them to your favorites!</p>
      <a href="index.php" class="btn btn-primary mt-2">
        <i class="fas fa-search me-1"></i>Browse Properties
      </a>
    </div>
  <?php else: ?>
    <div class="house-grid">
      <?php foreach ($favorite_houses as $house): ?>
        <div class="card house-card">
          <div class="position-relative">
            <img src="<?= e($house['image'] ?: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=600&q=80') ?>" class="card-img-top" alt="<?= e($house['title']) ?>">
            <div class="position-absolute top-0 end-0 m-2">
              <span class="badge bg-primary">Favorite</span>
            </div>
          </div>
          <div class="card-body">
            <h5 class="card-title"><?= e($house['title']) ?></h5>
            <p class="card-text text-muted"><?= e(substr($house['description'], 0, 120)) ?><?= strlen($house['description']) > 120 ? '...' : '' ?></p>
            
            <div class="d-flex justify-content-between mb-3">
              <div class="meta-item">
                <i class="fas fa-ruler-combined meta-icon text-primary"></i>
                <span><?= e($house['size']) ?></span>
              </div>
              <div class="meta-item">
                <i class="fas fa-tag meta-icon text-primary"></i>
                <span><?= e($house['price'] ?: 'Contact for price') ?></span>
              </div>
            </div>
            
            <div class="d-flex gap-2">
              <a href="house.php?id=<?= (int)$house['id'] ?>" class="btn btn-primary flex-grow-1">
                <i class="fas fa-eye me-1"></i>View Details
              </a>
              <a href="toggle_favorite.php?id=<?= (int)$house['id'] ?>&return=favorites" class="btn btn-outline-danger">
                <i class="fas fa-heart-broken"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
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