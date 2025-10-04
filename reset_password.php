<?php
require 'db.php';
require 'helpers.php';

// If already logged in, redirect to index
if (is_logged_in()) {
  header('Location: index.php');
  exit;
}

$err = '';
$success = '';
$valid_token = false;
$user_id = null;

// Check if token is provided and valid
$token = $_GET['token'] ?? '';
if ($token) {
  $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
  $stmt->execute([$token]);
  $user = $stmt->fetch();
  
  if ($user && strtotime($user['reset_expires']) > time()) {
    $valid_token = true;
    $user_id = $user['id'];
  } else {
    $err = 'Invalid or expired reset token.';
  }
} else {
  $err = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token';
  } else {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!$password || !$confirm_password) {
      $err = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
      $err = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
      $err = 'Password must be at least 8 characters long.';
    } else {
      // Update password and clear reset token
      $hashed = password_hash($password, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
      
      if ($stmt->execute([$hashed, $user_id])) {
        $success = 'Password reset successfully. You can now <a href="login.php">login</a> with your new password.';
        $valid_token = false; // Token is now invalid
      } else {
        $err = 'Error resetting password. Please try again.';
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
  <title>Reset Password - DreamHomes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    .wider-auth-container {
      max-width: 500px;
      margin: 2rem auto;
      padding: 2.5rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .wider-auth-title {
      text-align: center;
      margin-bottom: 2rem;
      color: #2c3e50;
      font-weight: 600;
    }
    
    .wider-form-group {
      margin-bottom: 1.5rem;
    }
    
    .wider-form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: #333;
    }
    
    .wider-form-control {
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #ddd;
      font-size: 1rem;
    }
    
    .wider-form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
    
    .wider-btn {
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
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
      <a href="login.php" class="btn btn-outline btn-sm">
        <i class="fas fa-sign-in-alt me-1"></i>Login
      </a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="wider-auth-container">
    <h3 class="wider-auth-title">
      <i class="fas fa-key me-2"></i>Set New Password
    </h3>
    
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?= $success ?>
      </div>
    <?php endif; ?>
    
    <?php if ($valid_token): ?>
      <p class="text-muted mb-4">Please enter your new password below.</p>
      
      <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        
        <div class="wider-form-group">
          <label class="wider-form-label" for="password">New Password</label>
          <input name="password" type="password" id="password" class="form-control wider-form-control" placeholder="Enter new password" required>
          <small class="text-muted">Use at least 8 characters with a mix of letters and numbers</small>
        </div>
        
        <div class="wider-form-group">
          <label class="wider-form-label" for="confirm_password">Confirm New Password</label>
          <input name="confirm_password" type="password" id="confirm_password" class="form-control wider-form-control" placeholder="Confirm new password" required>
        </div>
        
        <button class="btn btn-primary wider-btn w-100 mb-3">
          <i class="fas fa-save me-1"></i>Reset Password
        </button>
      </form>
    <?php else: ?>
      <div class="text-center">
        <p class="text-muted">You can <a href="forgot_password.php">request a new reset link</a> if needed.</p>
      </div>
    <?php endif; ?>
    
    <div class="text-center mt-3">
      <span class="text-muted">Remember your password?</span>
      <a href="login.php" class="text-decoration-none">Login now</a>
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