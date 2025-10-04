<?php
require 'db.php';
require 'helpers.php';

// Include PHPMailer files
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF';
  } else {
    $username = trim($_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
      $err = 'Please fill in all fields correctly';
    } else {
      // Check if email already exists
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $err = 'Email already registered';
      } else {
        // Hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed])) {
          // Send welcome email
          $mail = new PHPMailer(true);
          try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '';       // ✅ apna Gmail likho
            $mail->Password   = '';    // ✅ Gmail App Password likho
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('', 'DreamHomes');
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to DreamHomes!';
            $mail->Body    = "Hi " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . 
                             ",<br><br>Thank you for registering at <b>DreamHomes</b>.<br>
                             We’re excited to have you on board! 🎉<br><br>
                             Best regards,<br>DreamHomes Team";

            $mail->send();
            $msg = "Registration successful! A welcome email has been sent.";
            header('location: login.php');
          } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            $msg = "Registration successful, but welcome email could not be sent.";
          }
        } else {
          $err = 'Registration failed. Please try again.';
        }
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
    <style>
body {
  background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
              url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1600&q=80');
  background-size: cover;
  background-position: center;
}
</style>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
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
    .wider-form-group { margin-bottom: 1.5rem; }
    .wider-form-label { font-weight: 500; margin-bottom: 0.5rem; color: #333; }
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
    .wider-btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="wider-auth-container">
    <h3 class="wider-auth-title">
      <i class="fas fa-user-plus me-2"></i>Create Your Account
    </h3>
    
    <?php if ($err): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
      </div>
    <?php endif; ?>
    
    <?php if ($msg): ?>
      <div class="alert alert-info">
        <i class="fas fa-envelope me-2"></i><?= e($msg) ?>
      </div>
    <?php endif; ?>
    
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="username">Username</label>
        <input name="username" type="text" id="username" class="form-control wider-form-control" placeholder="Enter your username" required value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="email">Email Address</label>
        <input name="email" type="email" id="email" class="form-control wider-form-control" placeholder="Enter your email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      
      <div class="wider-form-group">
        <label class="wider-form-label" for="password">Password</label>
        <input name="password" type="password" id="password" class="form-control wider-form-control" placeholder="Create a password" required>
        <small class="text-muted">Use at least 8 characters with a mix of letters and numbers</small>
      </div>
      
      <button class="btn btn-primary wider-btn w-100 mb-3">
        <i class="fas fa-user-plus me-1"></i>Register
      </button>
      
      <div class="text-center">
        <span class="text-muted">Already have an account?</span>
        <a href="login.php" class="text-decoration-none">Login now</a>
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
