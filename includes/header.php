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
    <title>Movie Watchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/movie_watchlist/css/style.css">
</head>
<body>
    <div class="navbar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="/movie_watchlist/images/logo.jpg" alt="Logo" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;">
            <a href="/movie_watchlist/index.php" class="logo">MovieWatchlist</a>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/movie_watchlist/watchlist/index.php">WATCHLIST</a>
                <a href="/movie_watchlist/profile.php">PROFILE</a>
                <a href="/movie_watchlist/logout.php">LOGOUT</a>
            <?php else: ?>
                <a href="/movie_watchlist/movies/index.php">FILMS</a>
                <a href="/movie_watchlist/lists.php">LISTS</a>
                <a href="/movie_watchlist/members.php">MEMBERS</a>
                <a href="/movie_watchlist/journal.php">JOURNAL</a>
                <a href="#" class="btn-create" id="showLoginBtn">SIGN IN</a>
                <a href="#" class="btn-create" id="showSignupBtn">CREATE ACCOUNT</a>
            <?php endif; ?>
            
            <form class="search-form" action="/movie_watchlist/movies/search.php" method="GET" style="display: inline-block; margin-left: 15px;">
                <input type="text" name="q" placeholder="Search movies..." class="search-input" style="background: #2c3440; border: none; border-radius: 20px; padding: 8px 16px; color: white; width: 160px;">
            </form>
        </div>
    </div>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content login-modal">
            <span class="close-modal" id="closeLogin">&times;</span>
            <h2 class="modal-title">Sign In</h2>
            
            <!-- Error message with close button -->
            <div id="loginError" class="modal-error" style="display: none;">
                <span class="error-text" id="loginErrorText"></span>
                <button class="error-close" onclick="document.getElementById('loginError').style.display='none';">&times;</button>
            </div>
            
            <form id="loginForm" method="POST" action="/movie_watchlist/login_process.php">
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="modal-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#">Forgotten?</a>
                </div>
                <button type="submit" class="modal-btn">SIGN IN</button>
            </form>
            <div class="modal-footer">
                Don't have an account? <a href="#" id="switchToSignup">Join MovieWatchlist</a>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content signup-modal">
            <span class="close-modal" id="closeSignup">&times;</span>
            <h2 class="modal-title">Join MovieWatchlist</h2>
            
            <!-- Error message with close button -->
            <div id="signupError" class="modal-error" style="display: none;">
                <span class="error-text" id="signupErrorText"></span>
                <button class="error-close" onclick="document.getElementById('signupError').style.display='none';">&times;</button>
            </div>
            
            <form id="signupForm" method="POST" action="/movie_watchlist/register_process.php">
                <input type="text" name="fullname" placeholder="Full name" required>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                
                <div class="terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required> I accept the 
                        <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="modal-btn">SIGN UP</button>
            </form>
            <div class="modal-footer">
                Already have an account? <a href="#" id="switchToLogin">Sign In</a>
            </div>
        </div>
    </div>