<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$reviewId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$movieId = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

$stmt = $pdo->prepare("DELETE FROM tblreview WHERE ReviewID = ? AND UserID = ?");
$stmt->execute([$reviewId, $_SESSION['user_id']]);

$_SESSION['review_success'] = 'Review deleted successfully!';
header("Location: details.php?id=$movieId");
exit();
?>