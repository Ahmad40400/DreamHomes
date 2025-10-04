<?php
require 'db.php';
require 'helpers.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$house_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$return_url = $_GET['return'] ?? 'index';

// Validate house exists
$stmt = $pdo->prepare("SELECT id FROM houses WHERE id = ?");
$stmt->execute([$house_id]);
$house = $stmt->fetch();

if (!$house) {
    $_SESSION['error'] = 'Property not found';
    header('Location: ' . $return_url . '.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if already favorited
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND house_id = ?");
$stmt->execute([$user_id, $house_id]);
$is_favorited = $stmt->fetch();

if ($is_favorited) {
    // Remove from favorites
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND house_id = ?");
    $stmt->execute([$user_id, $house_id]);
    $_SESSION['success'] = 'Removed from favorites';
} else {
    // Add to favorites
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, house_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $house_id]);
    $_SESSION['success'] = 'Added to favorites';
}

header('Location: ' . $return_url . '.php');
exit;