<?php
require 'helpers.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $err = 'Invalid CSRF token';
  } else {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!$name || !$email || !$subject || !$message) {
      $err = 'Please fill in all fields';
    } else {
      // ✅ Send Email with PHPMailer
      $mail = new PHPMailer(true);
      try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '';  // 🔑 Your Gmail
        $mail->Password   = '';     // 🔑 App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 000;

        // Sender & recipient
        $mail->setFrom($email, $name);  // User who filled form
        $mail->addAddress('', 'DreamHomes Admin'); // Where it will be sent
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
          <h3>New Contact Form Message</h3>
          <p><strong>Name:</strong> {$name}</p>
          <p><strong>Email:</strong> {$email}</p>
          <p><strong>Subject:</strong> {$subject}</p>
          <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        $mail->AltBody = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\nMessage:\n{$message}";

        $mail->send();
        $success = 'Thank you for your message! We will get back to you soon.';
        $_POST = []; // Clear form
      } catch (Exception $e) {
        $err = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Contact Us - DreamHomes</title>
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
          <a href="contact.php" class="nav-link active">Contact Us</a>
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
  <div class="row">
    <div class="col-lg-10 mx-auto">
      <h1 class="text-center mb-4">Contact DreamHomes</h1>
      <p class="text-center text-muted mb-5">Have questions or need assistance? We're here to help you find your dream home.</p>
      
      <?php if ($err): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle me-2"></i><?= e($success) ?>
        </div>
      <?php endif; ?>
      
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-body">
              <h3 class="card-title mb-4">Send us a Message</h3>
              <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label class="form-label" for="name">Your Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" value="<?= e($_POST['name'] ?? '') ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label class="form-label" for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?= e($_POST['email'] ?? '') ?>" required>
                    </div>
                  </div>
                </div>
                
                <div class="form-group mb-3">
                  <label class="form-label" for="subject">Subject</label>
                  <input type="text" class="form-control" id="subject" name="subject" placeholder="What is this regarding?" value="<?= e($_POST['subject'] ?? '') ?>" required>
                </div>
                
                <div class="form-group mb-4">
                  <label class="form-label" for="message">Message</label>
                  <textarea class="form-control" id="message" name="message" rows="5" placeholder="How can we help you?" required><?= e($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-paper-plane me-1"></i>Send Message
                </button>
              </form>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card mb-4">
            <div class="card-body text-center">
              <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-map-marker-alt fa-lg"></i>
              </div>
              <h5>Our Office</h5>
              <p class="text-muted">123 Property Street<br>Real Estate City, RC 12345</p>
            </div>
          </div>
          
          <div class="card mb-4">
            <div class="card-body text-center">
              <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-phone fa-lg"></i>
              </div>
              <h5>Call Us</h5>
              <p class="text-muted">+1 (555) 123-4567<br>Mon-Fri, 9am-5pm</p>
            </div>
          </div>
          
          <div class="card">
            <div class="card-body text-center">
              <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-envelope fa-lg"></i>
              </div>
              <h5>Email Us</h5>
              <p class="text-muted">info@dreamhomes.com<br>support@dreamhomes.com</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mt-5">
        <div class="card-body">
          <h3 class="card-title text-center mb-4">Frequently Asked Questions</h3>
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  How do I list my property on DreamHomes?
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  To list your property, you need to create an account and verify your identity. Once registered, you can navigate to the "Add Listing" section from your dashboard and fill out the property details form. Our team will review your submission before it goes live on our platform.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Is there a fee for using DreamHomes?
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  For buyers and renters, DreamHomes is completely free to use. Property owners and agents pay a small listing fee only when their property is successfully sold or rented through our platform. There are no upfront costs or subscription fees.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  How can I schedule a property viewing?
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  You can schedule a viewing directly through the property listing page. Click on the "Schedule Viewing" button, select your preferred date and time, and provide your contact information. The property owner or agent will confirm your appointment via email or phone.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
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
