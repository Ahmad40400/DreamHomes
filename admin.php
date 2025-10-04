<?php
require 'db.php';
require 'helpers.php';

if (!is_logged_in() || !is_admin()) {
  header('Location: index.php');
  exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user'];
    if ($user_id && $user_id !== $_SESSION['user_id']) {
        // Delete user's favorites first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
    } else {
        $_SESSION['error'] = 'Cannot delete your own account or invalid user';
    }
    header('Location: admin.php');
    exit;
}

// Handle user role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    if ($user_id && $user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        if ($stmt->execute([$is_admin, $user_id])) {
            $_SESSION['success'] = 'User role updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user role';
        }
    } else {
        $_SESSION['error'] = 'Cannot modify your own admin status';
    }
    header('Location: admin.php');
    exit;
}

// Get statistics for the dashboard
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$houses_count = $pdo->query("SELECT COUNT(*) FROM houses")->fetchColumn();
$favorites_count = $pdo->query("SELECT COUNT(*) FROM favorites")->fetchColumn();

// Get recent users
$recent_users = $pdo->query("SELECT id, name, email, is_admin, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get recent houses
$recent_houses = $pdo->query("SELECT title, size, price, created_at FROM houses ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get all users for user management
$all_users = $pdo->query("SELECT id, name, email, is_admin, created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard - DreamHomes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    .stat-card {
      border-radius: 12px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
      font-size: 2.5rem;
      opacity: 0.8;
    }
    
    .recent-item {
      border-left: 3px solid transparent;
      transition: all 0.3s ease;
    }
    
    .recent-item:hover {
      border-left-color: #0d6efd;
      background-color: #f8f9fa;
    }
    
    .admin-badge {
      font-size: 0.7rem;
      padding: 0.25rem 0.5rem;
    }
    
    .user-actions {
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .user-item:hover .user-actions {
      opacity: 1;
    }
    #quickactions{
        color: #ffff;
    }
    #usermanagement{
        color: #ffff;
    }
    #recentproperties{
        color: #ffff;
    }
    #recentusers{
        color: #ffff;
    }
  </style>
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
            <a href="admin.php" class="nav-link active">Admin Dashboard</a>
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
                <li><a class="dropdown-item" href="admin.php"><i class="fas fa-chart-line me-2"></i>Admin Dashboard</a></li>
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

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Admin Dashboard</h1>
    <span class="badge bg-primary">Administrator</span>
  </div>
  
  <!-- Display success/error messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i><?= e($_SESSION['success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i><?= e($_SESSION['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  
  <!-- Statistics Cards -->
  <div class="row mb-5">
    <div class="col-md-3 mb-3">
      <div class="card stat-card bg-primary text-white">
        <div class="card-body d-flex align-items-center">
          <div class="me-3">
            <i class="fas fa-users stat-icon"></i>
          </div>
          <div>
            <h5 class="card-title">Total Users</h5>
            <h2 class="mb-0"><?= e($users_count) ?></h2>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card stat-card bg-success text-white">
        <div class="card-body d-flex align-items-center">
          <div class="me-3">
            <i class="fas fa-home stat-icon"></i>
          </div>
          <div>
            <h5 class="card-title">Total Properties</h5>
            <h2 class="mb-0"><?= e($houses_count) ?></h2>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card stat-card bg-info text-white">
        <div class="card-body d-flex align-items-center">
          <div class="me-3">
            <i class="fas fa-heart stat-icon"></i>
          </div>
          <div>
            <h5 class="card-title">Total Favorites</h5>
            <h2 class="mb-0"><?= e($favorites_count) ?></h2>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card stat-card bg-warning text-white">
        <div class="card-body d-flex align-items-center">
          <div class="me-3">
            <i class="fas fa-user-shield stat-icon"></i>
          </div>
          <div>
            <h5 class="card-title">Admin Users</h5>
            <h2 class="mb-0">
              <?php 
                $admin_count = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1")->fetchColumn();
                echo e($admin_count);
              ?>
            </h2>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <!-- Recent Users -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <h5 id="recentusers" class="mb-0">Recent Users</h5>
          <a href="#user-management" class="text-white"><i class="fas fa-users"></i></a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent_users)): ?>
            <div class="text-center py-4">
              <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">No users found</p>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recent_users as $user): ?>
                <div class="list-group-item recent-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <h6 class="mb-0"><?= e($user['name']) ?>
                        <?php if ($user['is_admin']): ?>
                          <span class="badge bg-primary admin-badge ms-1">Admin</span>
                        <?php endif; ?>
                      </h6>
                      <small class="text-muted"><?= e($user['email']) ?></small>
                    </div>
                    <small class="text-muted"><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer text-center">
          <a href="#user-management" class="btn btn-sm btn-outline-primary">View All Users</a>
        </div>
      </div>
    </div>
    
    <!-- Recent Properties -->
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
          <h5 id="recentproperties" class="mb-0">Recent Properties</h5>
          <a href="add_house.php" class="text-white"><i class="fas fa-plus"></i></a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recent_houses)): ?>
            <div class="text-center py-4">
              <i class="fas fa-home fa-2x text-muted mb-2"></i>
              <p class="text-muted mb-0">No properties found</p>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recent_houses as $house): ?>
                <div class="list-group-item recent-item">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <h6 class="mb-0"><?= e($house['title']) ?></h6>
                      <div class="d-flex text-muted small">
                        <span class="me-2"><?= e($house['size']) ?></span>
                        <span><?= e($house['price'] ?: 'Price on request') ?></span>
                      </div>
                    </div>
                    <small class="text-muted"><?= date('M j, Y', strtotime($house['created_at'])) ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer text-center">
          <a href="index.php" class="btn btn-sm btn-outline-primary me-2">View All Properties</a>
          <a href="add_house.php" class="btn btn-sm btn-primary">Add New Property</a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- User Management Section -->
  <div class="card mt-4" id="user-management">
    <div class="card-header bg-dark text-white">
      <h5 id="usermanagement" class="mb-0">User Management</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($all_users)): ?>
              <tr>
                <td colspan="5" class="text-center py-4">
                  <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                  <p class="text-muted mb-0">No users found</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($all_users as $user): ?>
                <tr class="user-item">
                  <td>
                    <?= e($user['name']) ?>
                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                      <span class="badge bg-info ms-1">You</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e($user['email']) ?></td>
                  <td>
                    <form method="post" class="d-inline">
                      <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                      <input type="hidden" name="update_role" value="1">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_admin" value="1" 
                          <?= $user['is_admin'] ? 'checked' : '' ?> 
                          <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : 'onchange="this.form.submit()"' ?>>
                        <label class="form-check-label">
                          <?= $user['is_admin'] ? 'Admin' : 'User' ?>
                        </label>
                      </div>
                    </form>
                  </td>
                  <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                  <td>
                    <div class="user-actions">
                      <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete_user=<?= $user['id'] ?>" class="btn btn-sm btn-danger" 
                          onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                          <i class="fas fa-trash"></i> Delete
                        </a>
                      <?php else: ?>
                        <span class="text-muted">Current user</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card mt-4">
    <div class="card-header bg-dark text-white">
      <h5 id="quickactions" class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
      <div class="row text-center">
        <div class="col-md-3 col-6 mb-3">
          <a href="add_house.php" class="btn btn-outline-primary w-100 py-3">
            <i class="fas fa-plus-circle fa-2x mb-2"></i>
            <h6>Add Property</h6>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
          <a href="index.php" class="btn btn-outline-success w-100 py-3">
            <i class="fas fa-list fa-2x mb-2"></i>
            <h6>Manage Properties</h6>
          </a>
        </div>
        <div class="col-md-3 col-6 mb-3">
          <a href="#user-management" class="btn btn-outline-info w-100 py-3">
            <i class="fas fa-users fa-2x mb-2"></i>
            <h6>Manage Users</h6>
          </a>
          
        </div>
        <div class="col-md-3 col-6 mb-3">
    <a href="admin.php?view_favorites=1" class="btn btn-outline-info w-100 py-3">
        <i class="fas fa-heart fa-2x mb-2"></i>
        <h6>View Favorites</h6>
    </a>
</div>
        <!-- In the Quick Actions section of admin.php -->
<!-- <div class="col-md-3 col-6 mb-3">
  <a href="#messages-management" class="btn btn-outline-info w-100 py-3">
    <i class="fas fa-envelope fa-2x mb-2"></i>
    <h6 id="viewmessages">View Messages</h6>
  </a>
</div> -->

      </div>
    </div>
  </div>
</div>
<?php
// Add this code to your admin.php file after the existing code

// Handle favorites display
if (isset($_GET['view_favorites'])) {
    $favorites_mode = true;
    
    // Get all favorites with user and house information
    $favorites = $pdo->query("
        SELECT 
            f.id as favorite_id,
            u.id as user_id,
            u.name as user_name,
            u.email as user_email,
            h.id as house_id,
            h.title as house_title,
            h.size as house_size,
            h.price as house_price,
            f.created_at as favorited_at
        FROM favorites f
        JOIN users u ON f.user_id = u.id
        JOIN houses h ON f.house_id = h.id
        ORDER BY f.created_at DESC
    ")->fetchAll();
} else {
    $favorites_mode = false;
}
?>

<!-- Add this to your admin.php file where you want the favorites management section -->
<?php if ($favorites_mode): ?>
<div class="card mt-4" id="favorites-management">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Favorites Management</h5>
        <a href="admin.php" class="btn btn-sm btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($favorites)): ?>
            <div class="text-center py-4">
                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                <h5>No favorites yet</h5>
                <p class="text-muted">Users haven't added any properties to their favorites yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Property</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Favorited On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favorites as $fav): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= e($fav['user_name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= e($fav['user_email']) ?></small>
                                </div>
                            </td>
                            <td>
                                <a href="house.php?id=<?= $fav['house_id'] ?>" target="_blank">
                                    <?= e($fav['house_title']) ?>
                                </a>
                            </td>
                            <td><?= e($fav['house_size']) ?></td>
                            <td><?= e($fav['house_price'] ?: 'Contact for price') ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($fav['favorited_at'])) ?></td>
                            <td>
                                <a href="toggle_favorite.php?admin_remove=1&id=<?= $fav['house_id'] ?>&user_id=<?= $fav['user_id'] ?>&return=admin%3Fview_favorites%3D1" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to remove this favorite?')">
                                    <i class="fas fa-trash"></i> Remove
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- Add this button to the Quick Actions section -->

<?php endif; ?>







<!-- Add this script before the closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle URL hash to scroll to specific sections
    function scrollToSection() {
        const hash = window.location.hash;
        if (hash) {
            // Small delay to ensure page is fully loaded
            setTimeout(() => {
                const element = document.querySelector(hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        }
    }
    
    // Initial scroll on page load
    scrollToSection();
    
    // Update URL hash when clicking on internal links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                // Update URL without page refresh
                history.pushState(null, null, href);
                scrollToSection();
            }
        });
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', scrollToSection);
    
    // For favorites view, maintain the state
    <?php if ($favorites_mode): ?>
        history.replaceState(null, null, '#favorites-management');
        scrollToSection();
    <?php endif; ?>
});
</script>






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
          <?php if (is_admin()): ?>
            <li><a href="admin.php" class="text-white">Admin Dashboard</a></li>
          <?php endif; ?>
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