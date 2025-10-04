<?php
require 'db.php';
require 'helpers.php';

if (!is_logged_in() || !is_admin()) {
  header('Location: index.php');
  exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch house details for confirmation
$stmt = $pdo->prepare("SELECT * FROM houses WHERE id = ?");
$stmt->execute([$id]);
$house = $stmt->fetch();

if (!$house) {
  $_SESSION['error'] = 'House not found';
  header('Location: index.php');
  exit;
}

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token';
  } else {
    // Delete the house from database
    $stmt = $pdo->prepare("DELETE FROM houses WHERE id = ?");
    if ($stmt->execute([$id])) {
      $_SESSION['success'] = 'Property deleted successfully';
      header('Location: index.php');
      exit;
    } else {
      $err = 'Failed to delete property. Please try again.';
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Delete Property</title>
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

<div class="container py-5">
  <div class="auth-container">
    <h3 class="auth-title text-danger">
      <i class="fas fa-exclamation-triangle me-2"></i>Delete Property
    </h3>
    
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($_SESSION['error']) ?>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="alert alert-warning">
      <h5 class="alert-heading">Are you sure you want to delete this property?</h5>
      <p class="mb-0">This action cannot be undone. All data related to this property will be permanently removed from the system.</p>
    </div>
    
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title"><?= e($house['title']) ?></h5>
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
        <p class="card-text text-muted"><?= e(substr($house['description'], 0, 120)) ?><?= strlen($house['description']) > 120 ? '...' : '' ?></p>
      </div>
    </div>
    
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline w-50">Cancel</a>
        <button type="submit" class="btn btn-danger w-50">
          <i class="fas fa-trash me-1"></i>Delete Property
        </button>
      </div>
    </form>
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