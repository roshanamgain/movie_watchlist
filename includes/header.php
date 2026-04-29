<!DOCTYPE html>
<html>
<head>
    <title>Movie Watchlist</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="logo">🎬 MovieWatchlist</a>
        <a href="index.php">Home</a>
        <a href="movies/index.php">Movies</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="watchlist/index.php">My Watchlist</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>