<?php
require 'db.php';
require 'helpers.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ?");
$stmt->execute([$id]);
$house = $stmt->fetch();

if (!$house) {
  http_response_code(404);
  echo "House not found";
  exit;
}

?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($house['title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="navbar">
  <div class="container navbar-content">
    <a class="navbar-brand" href="index.php">
      <i class="fas fa-home me-2"></i>DreamHomes
    </a>
    <div>
      <a href="index.php" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Listings
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="card">
    <?php
// Check if image is a URL or a local path
if (!empty($house['image'])) {
    if (filter_var($house['image'], FILTER_VALIDATE_URL)) {
        $image_src = $house['image'];
    } else {
        // It's a local file path
        $image_src = '/' . $house['image'];
    }
} else {
    $image_src = 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&q=80';
}
?>

<img src="<?= e($image_src) ?>" class="house-detail-img" alt="House image">
    
    <div class="card-body">
      <h1 class="house-detail-title"><?= e($house['title']) ?></h1>
      
      <div class="house-meta">
        <div class="meta-item">
          <i class="fas fa-ruler-combined meta-icon text-primary"></i>
          <span><?= e($house['size']) ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-tag meta-icon text-primary"></i>
          <span><?= e($house['price'] ?: 'Contact for price') ?></span>
        </div>
        <div class="meta-item">
          <i class="fas fa-calendar meta-icon text-primary"></i>
          <span>Added: <?= date('M j, Y', strtotime($house['created_at'])) ?></span>
        </div>
      </div>
      
      <div class="border-top pt-4 mt-4">
        <h4 class="mb-3">Property Description</h4>
        <p class="lead"><?= nl2br(e($house['description'])) ?></p>
      </div>
      
     <div class="d-flex gap-2 mt-5">
  <a href="index.php" class="btn btn-outline">
    <i class="fas fa-arrow-left me-1"></i>Back to Listings
  </a>
  
  <?php if (is_logged_in()): ?>
    <?php
    // Check if this house is in user's favorites
    $is_favorited = false;
    if (is_logged_in()) {
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND house_id = ?");
        $stmt->execute([$_SESSION['user_id'], $house['id']]);
        $is_favorited = $stmt->fetch();
    }
    ?>
    <!-- <a href="toggle_favorite.php?id=<?= (int)$house['id'] ?>&return=house" class="btn <?= $is_favorited ? 'btn-danger' : 'btn-outline-danger' ?>">
      <i class="fas <?= $is_favorited ? 'fa-heart' : 'fa-heart' ?> me-1"></i>
      <?= $is_favorited ? 'Remove Favorite' : 'Add to Favorites' ?>
    </a> -->
  <?php endif; ?>
  
  <a href="contact.php" class="btn btn-primary">
    <i class="fas fa-phone me-1"></i>Contact Owner
  </a>
  
  <?php if (is_logged_in() && is_admin()): ?>
    <a href="delete_house.php?id=<?= (int)$house['id'] ?>" class="btn btn-danger">
      <i class="fas fa-trash me-1"></i>Delete Property
    </a>
  <?php endif; ?>
</div>
    </div>
  </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-0">&copy; <?= date('Y') ?> DreamHomes. All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>