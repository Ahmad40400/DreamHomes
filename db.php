<?php
// db.php
$host = "localhost";
$dbname = "house_listing"; // apna database name likho
$username = "root";    // apna MySQL username
$password = "";        // apna MySQL password (xampp me by default empty hota hai)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>