<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movieId = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
    $userId = $_SESSION['user_id'];
    
    if ($movieId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid movie']);
        exit();
    }
    
    // Check if already in watchlist
    $stmt = $pdo->prepare("SELECT * FROM tblwatchlist WHERE UserID = ? AND MovieID = ?");
    $stmt->execute([$userId, $movieId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'Movie already in your watchlist']);
        exit();
    }
    
    // Add to watchlist
    $stmt = $pdo->prepare("INSERT INTO tblwatchlist (UserID, MovieID, Status, AddedDate) VALUES (?, ?, 'To Watch', CURDATE())");
    if ($stmt->execute([$userId, $movieId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit();
}
?>