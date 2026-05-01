<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

if (isset($_GET['watchlist_id']) && is_numeric($_GET['watchlist_id'])) {
    $watchlist_id = $_GET['watchlist_id'];
    // Ensure the entry belongs to the logged-in user
    $delete = $pdo->prepare("DELETE FROM watchlist WHERE id = ? AND user_id = ?");
    $delete->execute([$watchlist_id, $_SESSION['user_id']]);
}
header('Location: watchlist.php');
exit;
?>