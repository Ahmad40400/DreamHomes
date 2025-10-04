<?php
session_start();

// Session timeout - 15 minutes (900 seconds)
$timeout = 900;

// Check if session is expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    // Last request was more than 2 minutes ago
    session_unset();     // unset $_SESSION variable 
    session_destroy();   // destroy session data
    session_start();     // start new session for potential redirect message
    $_SESSION['session_expired'] = true;
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function verify_csrf($token) {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function e($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function is_logged_in() {
  return !empty($_SESSION['user_id']) && !empty($_SESSION['LAST_ACTIVITY']);
}

// Check if user is admin
function is_admin() {
  return !empty($_SESSION['is_admin']);
}
?>