<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

if (isset($_GET['movie_id']) && is_numeric($_GET['movie_id'])) {
    $movie_id = $_GET['movie_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already exists
    $check = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $check->execute([$user_id, $movie_id]);
    if (!$check->fetch()) {
        $insert = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
        $insert->execute([$user_id, $movie_id]);
    }
}
header('Location: movies.php');
exit;
?>