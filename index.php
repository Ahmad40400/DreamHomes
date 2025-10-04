<?php
require 'db.php';
require 'helpers.php';

// Pagination logic
$items_per_page = 9;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// Calculate offset
$offset = ($current_page - 1) * $items_per_page;

// Get total number of houses
$stmt = $pdo->query("SELECT COUNT(*) FROM houses");
$total_houses = $stmt->fetchColumn();

// Calculate total pages
$total_pages = ceil($total_houses / $items_per_page);

// Get houses for current page
$stmt = $pdo->prepare("SELECT * FROM houses ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$houses = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>House Listings</title>
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
          <a href="index.php" class="nav-link active">Home</a>
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

<div class="hero">
  <div class="container text-center">
    <h1 id="banner-heading">Find Your Dream Home</h1>
    <p>Browse through our curated collection of beautiful properties</p>
    <?php if (!is_logged_in()): ?>
      <a href="register.php" class="btn btn-light">
        <i class="fas fa-user-plus me-1"></i>Join Now
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3">Available Properties</h2>
    <?php if (is_admin()): ?>
      <a class="btn btn-success" href="add_house.php">
        <i class="fas fa-plus me-1"></i>Add New House
      </a>
    <?php endif; ?>
  </div>

  <div class="house-grid">
    <?php if (empty($houses)): ?>
      <div class="col-12">
        <div class="card text-center py-5">
          <i class="fas fa-home fa-3x text-muted mb-3"></i>
          <h3 class="h5">No houses listed yet</h3>
          <p class="text-muted">Be the first to add a property listing</p>
          <?php if (is_admin()): ?>
            <a href="add_house.php" class="btn btn-primary mt-2">Add Your First House</a>
          <?php else: ?>
            <a href="index.php" class="btn btn-primary mt-2">No Property Available</a>
          <?php endif; ?>
        </div>
      </div>
    <?php else:
      foreach ($houses as $house):
    ?>
    
      <div class="card house-card">
        <div class="position-relative">
          <img src="<?= e($house['image'] ?: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=600&q=80') ?>" class="card-img-top" alt="<?= e($house['title']) ?>">
          <div class="position-absolute top-0 end-0 m-2">
            <span class="badge bg-primary">New</span>
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
  
  <?php if (is_logged_in()): ?>
    <?php
    // Check if this house is in user's favorites
    $is_favorited = false;
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND house_id = ?");
    $stmt->execute([$_SESSION['user_id'], $house['id']]);
    $is_favorited = $stmt->fetch();
    ?>
    <a href="toggle_favorite.php?id=<?= (int)$house['id'] ?>&return=index" class="btn <?= $is_favorited ? 'btn-danger' : 'btn-outline-danger' ?>" title="<?= $is_favorited ? 'Remove from favorites' : 'Add to favorites' ?>">
      <i class="fas <?= $is_favorited ? 'fa-heart' : 'fa-heart' ?>"></i>
    </a>
  <?php endif; ?>
</div>
        </div>
      </div>
    <?php 
      endforeach;
    endif; 
    ?>
  </div>

  <!-- Pagination Controls -->
  <?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-between align-items-center mt-5">
      <!-- Pagination Info -->
      <p class="text-muted mb-0">
        Showing <?= count($houses) ?> of <?= $total_houses ?> properties
      </p>
      
      <!-- Pagination Navigation -->
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center mb-0">
          <!-- Previous Page Link -->
          <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>

          <!-- Page Number Links -->
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <!-- Next Page Link -->
          <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        </ul>
      </nav>

      <!-- Page Info -->
      <p class="text-muted mb-0 d-none d-md-block">
        Page <?= $current_page ?> of <?= $total_pages ?>
      </p>
    </div>
  <?php else: ?>
    <!-- Show only the info when there's only one page -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <p class="text-muted mb-0">
        Showing <?= count($houses) ?> of <?= $total_houses ?> properties
      </p>
      <?php if ($total_pages > 1): ?>
        <p class="text-muted mb-0">
          Page <?= $current_page ?> of <?= $total_pages ?>
        </p>
      <?php endif; ?>
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

<!-- Login Reminder Modal -->
<div class="modal fade" id="loginReminderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Join DreamHomes Community</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <i class="fas fa-home fa-3x text-primary mb-3"></i>
        <h4>Find Your Dream Home</h4>
        <p class="text-muted">Sign up or login to access exclusive property listings and save your favorites</p>
      </div>
      <div class="modal-footer justify-content-center">
        <a href="login.php" class="btn btn-primary me-2">
          <i class="fas fa-sign-in-alt me-1"></i>Login
        </a>
        <a href="register.php" class="btn btn-outline-primary">
          <i class="fas fa-user-plus me-1"></i>Sign Up
        </a>
      </div>
    </div>
  </div>
</div>

<script>
// Show login reminder modal after 10 seconds if user is not logged in
document.addEventListener('DOMContentLoaded', function() {
  // Check if user is logged in (using PHP session status)
  const isLoggedIn = <?= is_logged_in() ? 'true' : 'false' ?>;
  
  if (!isLoggedIn) {
    setTimeout(function() {
      const loginModal = new bootstrap.Modal(document.getElementById('loginReminderModal'));
      loginModal.show();
    }, 2000); // 2 seconds = 2000 milliseconds
  }
});
</script>
</body>
</html>