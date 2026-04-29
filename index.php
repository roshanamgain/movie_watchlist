<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container">
    <h1>Welcome to Movie Watchlist</h1>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Hello, <?php echo $_SESSION['fullname']; ?>!</p>
        <div class="menu">
            <a href="profile.php">My Profile</a>
            <a href="movies/index.php">Browse Movies</a>
            <a href="watchlist/index.php">My Watchlist</a>
            <a href="logout.php">Logout</a>
        </div>
    <?php else: ?>
        <div class="menu">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
            <a href="movies/index.php">Browse Movies</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>