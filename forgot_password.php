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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token';
  } else {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if ($email) {
      // Check if email exists
      $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch();
      
      if ($user) {
        // Generate reset token (valid for 1 hour)
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
        
        // Store token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        if ($stmt->execute([$token, $expires, $user['id']])) {
          // In a real application, you would send an email here
          // For this example, we'll just show the reset link
          $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token";
          $success = "Password reset instructions have been sent to your email. For demo: <a href='$reset_link'>$reset_link</a>";
        } else {
          $err = 'Error generating reset token. Please try again.';
        }
      } else {
        // For security, don't reveal if email exists or not
        $success = 'If your email exists in our system, you will receive password reset instructions.';
      }
    } else {
      $err = 'Please provide a valid email address.';
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Forgot Password - DreamHomes</title>
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
      <i class="fas fa-key me-2"></i>Reset Your Password
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
    
    <p class="text-muted mb-4">Enter your email address and we'll send you instructions to reset your password.</p>
    
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control wider-form-control" placeholder="Enter your email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      
      <button class="btn btn-primary wider-btn w-100 mb-3">
        <i class="fas fa-paper-plane me-1"></i>Send Reset Instructions
      </button>
      
      <div class="text-center">
        <span class="text-muted">Remember your password?</span>
        <a href="login.php" class="text-decoration-none">Login now</a>
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