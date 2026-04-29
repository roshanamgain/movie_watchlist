<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container">
    <h1>Welcome to Movie Watchlist</h1>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Hello, <?php echo $_SESSION['fullname']; ?>!</p>
        <p>Start exploring movies and building your watchlist.</p>
    <?php else: ?>
        <p>Please login or register to start managing your movie watchlist.</p>
        <div class="menu" style="margin-top: 20px;">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
            <a href="movies/index.php" class="btn btn-secondary">Browse Movies</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>