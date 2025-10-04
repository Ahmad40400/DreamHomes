<?php
require 'db.php';
require 'helpers.php';

if (!is_logged_in() || !is_admin()) {
  header('Location: index.php');
  exit;
}

$err = '';
$upload_dir = 'uploads/';
// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF';
  } else {
    $title = trim($_POST['title'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $image_url = filter_var(trim($_POST['image'] ?? ''), FILTER_SANITIZE_URL);
    $image_path = '';

    if (!$title || !$size) {
      $err = 'Title and size are required';
    } else {
      // Handle file upload
      if (!empty($_FILES['image_upload']['name'])) {
        $file_name = $_FILES['image_upload']['name'];
        $file_tmp = $_FILES['image_upload']['tmp_name'];
        $file_size = $_FILES['image_upload']['size'];
        $file_error = $_FILES['image_upload']['error'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if ($file_error !== UPLOAD_ERR_OK) {
          $err = 'File upload error: ' . $file_error;
        } elseif (!in_array($file_ext, $allowed)) {
          $err = 'File type not allowed. Please upload JPG, PNG, GIF, or WebP images.';
        } elseif ($file_size > 5000000) { // 5MB limit
          $err = 'File size too large. Maximum size is 5MB.';
        } else {
          // Generate unique filename
          $new_filename = uniqid('', true) . '.' . $file_ext;
          $destination = $upload_dir . $new_filename;
          
          if (move_uploaded_file($file_tmp, $destination)) {
            $image_path = $destination;
          } else {
            $err = 'Failed to upload file.';
          }
        }
      }
      
      // If no upload error and we have either URL or uploaded file
      if (!$err) {
        // Use uploaded file if available, otherwise use URL
        $image_to_store = $image_path ? $image_path : ($image_url ?: '');
        
        // Validate URL if provided (and no file was uploaded)
        if (!$image_path && $image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) {
          $err = 'Please provide a valid image URL';
        } else {
          $stmt = $pdo->prepare("INSERT INTO houses (title, size, price, description, image) VALUES (?, ?, ?, ?, ?)");
          if ($stmt->execute([$title, $size, $price, $desc, $image_to_store])) {
            header('Location: index.php');
            exit;
          } else {
            $err = 'Failed to add house. Please try again.';
          }
        }
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    /* Custom styles for wider form */
    .wider-form-container {
      max-width: 700px;
      margin: 0 auto;
      padding: 2rem;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .wider-form-container .form-group {
      margin-bottom: 1.5rem;
    }
    
    .wider-form-container .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: #333;
    }
    
    .wider-form-container .form-control {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #ddd;
      font-size: 1rem;
    }
    
    .wider-form-container .form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .wider-form-container .btn {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
    }
    
    .navbar {
      background-color: #2c3e50;
      padding: 1rem 0;
    }
    
    .navbar-brand {
      color: #fff !important;
      font-weight: 600;
      font-size: 1.5rem;
    }
    
    .btn-outline {
      border-color: #fff;
      color: #fff;
    }
    
    .btn-outline:hover {
      background-color: #fff;
      color: #2c3e50;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }
  </style>
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
  <div class="wider-form-container">
    <h3 class="text-center mb-4" style="color: #2c3e50; font-weight: 600;">Add New Property</h3>
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
      </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <div class="form-group">
        <label class="form-label" for="title">Property Title *</label>
        <input name="title" id="title" class="form-control" placeholder="e.g., Beautiful 5 Marla House in Gulberg" required value="<?= e($_POST['title'] ?? '') ?>">
      </div>
      
      <div class="form-group">
        <label class="form-label" for="size">Size *</label>
        <input name="size" id="size" class="form-control" placeholder="e.g., 5 Marla" required value="<?= e($_POST['size'] ?? '') ?>">
      </div>
      
      <div class="form-group">
        <label class="form-label" for="price">Price</label>
        <input name="price" id="price" class="form-control" placeholder="e.g., Rs. 45 Lakh" value="<?= e($_POST['price'] ?? '') ?>">
      </div>
      
      <div class="form-group">
        <label class="form-label" for="image">Image URL</label>
        <input name="image" type="url" id="image" class="form-control" placeholder="https://example.com/image.jpg" value="<?= e($_POST['image'] ?? '') ?>">
        <small class="text-muted">Leave empty to use a default image</small>
      </div>
      
      <div class="form-group">
        <label class="form-label">Or Upload Image</label>
        <input name="image_upload" type="file" id="image_upload" class="form-control" accept="image/*">
        <small class="text-muted">Max file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP</small>
      </div>
      
      <div class="form-group">
        <label class="form-label" for="description">Description</label>
        <textarea name="description" id="description" class="form-control" placeholder="Describe the property in detail" rows="4"><?= e($_POST['description'] ?? '') ?></textarea>
      </div>
      
      <div class="d-grid gap-2">
        <button class="btn btn-primary">
          <i class="fas fa-plus-circle me-1"></i>Add Property
        </button>
        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
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