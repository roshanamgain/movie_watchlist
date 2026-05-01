<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

if (isset($_GET['watchlist_id']) && is_numeric($_GET['watchlist_id'])) {
    $watchlist_id = $_GET['watchlist_id'];
    // Toggle the watched status
    $stmt = $pdo->prepare("UPDATE watchlist SET watched = NOT watched WHERE id = ? AND user_id = ?");
    $stmt->execute([$watchlist_id, $_SESSION['user_id']]);
}
header('Location: watchlist.php');
exit;
?>