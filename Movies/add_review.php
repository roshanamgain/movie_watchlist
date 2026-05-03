<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $movieId = $_POST['movie_id'];
    $rating = $_POST['rating'];
    $reviewText = trim($_POST['review_text']);
    $reviewDate = date('Y-m-d');
    
    if (isset($_POST['add_review'])) {
        // Check if already reviewed
        $stmt = $pdo->prepare("SELECT * FROM tblreview WHERE UserID = ? AND MovieID = ?");
        $stmt->execute([$userId, $movieId]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['review_error'] = 'You have already reviewed this movie.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO tblreview (UserID, MovieID, Rating, ReviewText, ReviewDate) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$userId, $movieId, $rating, $reviewText, $reviewDate])) {
                $_SESSION['review_success'] = 'Review added successfully!';
            } else {
                $_SESSION['review_error'] = 'Failed to add review.';
            }
        }
    }
    
    if (isset($_POST['update_review'])) {
        $stmt = $pdo->prepare("UPDATE tblreview SET Rating = ?, ReviewText = ? WHERE UserID = ? AND MovieID = ?");
        if ($stmt->execute([$rating, $reviewText, $userId, $movieId])) {
            $_SESSION['review_success'] = 'Review updated successfully!';
        } else {
            $_SESSION['review_error'] = 'Failed to update review.';
        }
    }
    
    header("Location: details.php?id=$movieId");
    exit();
}
?>