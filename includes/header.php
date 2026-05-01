<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Watchlist - Track films you've watched</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/movie_watchlist/css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="/movie_watchlist/index.php" class="logo">🎭 MovieWatchlist</a>
        <div class="nav-links">
            <a href="/movie_watchlist/movies/index.php">FILMS</a>
            <a href="/movie_watchlist/watchlist/index.php">WATCHLIST</a>
            <a href="/movie_watchlist/lists.php">LISTS</a>
            <a href="/movie_watchlist/members.php">MEMBERS</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/movie_watchlist/profile.php"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Profile'); ?></a>
                <a href="/movie_watchlist/logout.php" class="btn-join">LOGOUT</a>
            <?php else: ?>
                <a href="/movie_watchlist/login.php">SIGN IN</a>
                <a href="/movie_watchlist/register.php" class="btn-join">JOIN THE HEIST</a>
            <?php endif; ?>
        </div>
    </div>