<?php
require 'db.php';
require 'helpers.php';

// If already logged in, redirect to index
if (is_logged_in()) {
  header('Location: index.php');
  exit;
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF';
  } else {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $pass = $_POST['password'] ?? '';
    
    if ($email && $pass) {
      $stmt = $pdo->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
      $stmt->execute([$email]);
      $user = $stmt->fetch();
      
      if ($user && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        $_SESSION['LAST_ACTIVITY'] = time(); // Initialize last activity
        
        header('Location: index.php');
        exit;
      } else {
        $err = 'Invalid credentials';
      }
    } else {
      $err = 'Provide valid email and password';
    }
  }
}

// Check for session expiration message
if (isset($_SESSION['session_expired'])) {
    $err = 'Your session has expired due to inactivity. Please login again.';
    unset($_SESSION['session_expired']);
}
?>
<!doctype html>
<html>
<head>
  <style>
body {
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
              url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1600&q=80');
  background-size: cover;
  background-position: center;
}
</style>









  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
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

<div class="container py-5">
  <div class="wider-auth-container">
    <h3 class="wider-auth-title">
      <i class="fas fa-sign-in-alt me-2"></i>Login to Your Account
    </h3>
    
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
      </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i><?= e($_SESSION['success']) ?>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control wider-form-control" placeholder="Enter your email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="password">Password</label>
        <input name="password" type="password" id="password" class="form-control wider-form-control" placeholder="Enter your password" required>
        <small class="text-muted">
          <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
        </small>
      </div>
      
      <button class="btn btn-primary wider-btn w-100 mb-3">
        <i class="fas fa-sign-in-alt me-1"></i>Login
      </button>
      
      <div class="text-center">
        <span class="text-muted">Don't have an account?</span>
        <a href="register.php" class="text-decoration-none">Register now</a>
      </div>
    </form>
  </div>
</div>

<!-- <footer class="bg-dark text-white py-4 mt-5">
  <div class="container text-center">
    <p class="mb-0">&copy; <?= date('Y') ?> DreamHomes. All rights reserved.</p>
  </div>
</footer> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>