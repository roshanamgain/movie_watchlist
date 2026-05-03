<?php
require_once 'includes/config.php';

// Fetch popular films from tblmovie
$stmt = $pdo->query("SELECT * FROM tblmovie ORDER BY TMDBRating DESC LIMIT 12");
$popularFilms = $stmt->fetchAll();

// Fetch recent reviews from tblreview
$stmt = $pdo->query("
    SELECT r.*, u.FullName as username, m.Title as film_title 
    FROM tblreview r 
    JOIN tbluser u ON r.UserID = u.UserID 
    JOIN tblmovie m ON r.MovieID = m.MovieID 
    ORDER BY r.ReviewDate DESC LIMIT 3
");
$recentReviews = $stmt->fetchAll();

// Get user stats if logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) as watchlist_count FROM tblwatchlist WHERE UserID = ?");
    $stmt->execute([$userId]);
    $watchlistCount = $stmt->fetch()['watchlist_count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as watched_count FROM tblwatchlist WHERE UserID = ? AND Status = 'Watched'");
    $stmt->execute([$userId]);
    $watchedCount = $stmt->fetch()['watched_count'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as review_count FROM tblreview WHERE UserID = ?");
    $stmt->execute([$userId]);
    $reviewCount = $stmt->fetch()['review_count'];
}

// Set different hero images based on login status
$loggedOutHeroImage = '/movie_watchlist/images/bg-image.jpg';
$loggedInHeroImage = '/movie_watchlist/images/bg-login.jpg';
?>

<?php include 'includes/header.php'; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- ============================================ -->
    <!-- LOGGED IN USER VIEW (Letterboxd Style)        -->
    <!-- ============================================ -->
    
    <div class="hero-section" style="background-image: url('<?php echo $loggedInHeroImage; ?>');">
        <div class="hero-content">
            <h1 class="hero-title">That's good! You've taken your<br>first step into a larger world...</h1>
            <p class="hero-subtitle">
                MovieWatchlist lets you keep track of every film you've seen, so you can instantly recommend<br>
                one the moment someone asks, or check reactions to a film you've just heard about.
            </p>
        </div>
    </div>

    <!-- User Stats Section -->
    <div class="user-stats-section">
        <div class="user-stats-grid">
            <div class="user-stat">
                <div class="user-stat-number"><?php echo $watchlistCount; ?></div>
                <div class="user-stat-label">in your watchlist</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-number"><?php echo $watchedCount; ?></div>
                <div class="user-stat-label">films watched</div>
            </div>
            <div class="user-stat">
                <div class="user-stat-number"><?php echo $reviewCount; ?></div>
                <div class="user-stat-label">reviews written</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="movie-section">
        <div class="section-header">
            <h2 class="section-title">RECENT ACTIVITY</h2>
            <a href="watchlist/index.php" class="section-link">VIEW ALL →</a>
        </div>
        <div class="film-strip">
            <?php
            $stmt = $pdo->prepare("
                SELECT w.*, m.Title, m.ReleaseYear, m.PosterURL 
                FROM tblwatchlist w 
                JOIN tblmovie m ON w.MovieID = m.MovieID 
                WHERE w.UserID = ? 
                ORDER BY w.AddedDate DESC LIMIT 8
            ");
            $stmt->execute([$userId]);
            $recentActivity = $stmt->fetchAll();
            ?>
            <?php if (count($recentActivity) > 0): ?>
                <?php foreach ($recentActivity as $item): ?>
                <div class="movie-card" onclick="location.href='movies/details.php?id=<?php echo $item['MovieID']; ?>'">
                    <div class="movie-poster">
                        <?php if (!empty($item['PosterURL'])): ?>
                            <img src="<?php echo $item['PosterURL']; ?>" alt="<?php echo htmlspecialchars($item['Title']); ?>" class="movie-poster-img">
                        <?php else: ?>
                            <span class="movie-poster-placeholder">🎬</span>
                        <?php endif; ?>
                    </div>
                    <div class="movie-info">
                        <div class="movie-title"><?php echo htmlspecialchars($item['Title']); ?></div>
                        <div class="movie-year"><?php echo $item['ReleaseYear']; ?></div>
                        <div class="watchlist-status-badge status-<?php echo strtolower(str_replace(' ', '-', $item['Status'])); ?>">
                            <?php echo $item['Status']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #99aabb;">Your watchlist is empty. <a href="movies/index.php" style="color: #00e054;">Browse movies</a> to get started!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popular Films Section -->
    <div class="movie-section">
        <div class="section-header">
            <h2 class="section-title">POPULAR THIS WEEK</h2>
            <a href="movies/index.php" class="section-link">ALL FILMS →</a>
        </div>
        <div class="film-strip">
            <?php foreach ($popularFilms as $film): ?>
            <div class="movie-card" onclick="location.href='movies/details.php?id=<?php echo $film['MovieID']; ?>'">
                <div class="movie-poster">
                    <?php if (!empty($film['PosterURL'])): ?>
                        <img src="<?php echo $film['PosterURL']; ?>" alt="<?php echo htmlspecialchars($film['Title']); ?>" class="movie-poster-img">
                    <?php else: ?>
                        <span class="movie-poster-placeholder">🎬</span>
                    <?php endif; ?>
                </div>
                <div class="movie-info">
                    <div class="movie-title"><?php echo htmlspecialchars($film['Title']); ?></div>
                    <div class="movie-year"><?php echo $film['ReleaseYear']; ?></div>
                    <div class="movie-rating">⭐ <?php echo $film['TMDBRating']; ?>/10</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php else: ?>
    <!-- ============================================ -->
    <!-- LOGGED OUT USER VIEW                          -->
    <!-- ============================================ -->
    
    <div class="hero-section" style="background-image: url('<?php echo $loggedOutHeroImage; ?>');">
        <div class="hero-content">
            <h1 class="hero-title">Track films you've watched.<br>Save those you want to see.<br>Tell your friends what's good.</h1>
        </div>
    </div>

    <div class="social-text-section">
        <a href="javascript:void(0)" class="social-btn" id="heroGetStartedBtn">GET STARTED — IT'S FREE!</a>
        <p class="social-text">The social network for film lovers.</p>
    </div>

    <div class="movie-section">
        <div class="section-header">
            <h2 class="section-title">POPULAR THIS WEEK</h2>
            <a href="movies/index.php" class="section-link">ALL FILMS →</a>
        </div>
        <div class="film-strip">
            <?php foreach ($popularFilms as $film): ?>
            <div class="movie-card" onclick="location.href='movies/details.php?id=<?php echo $film['MovieID']; ?>'">
                <div class="movie-poster">
                    <?php if (!empty($film['PosterURL'])): ?>
                        <img src="<?php echo $film['PosterURL']; ?>" alt="<?php echo htmlspecialchars($film['Title']); ?>" class="movie-poster-img">
                    <?php else: ?>
                        <span class="movie-poster-placeholder">🎬</span>
                    <?php endif; ?>
                </div>
                <div class="movie-info">
                    <div class="movie-title"><?php echo htmlspecialchars($film['Title']); ?></div>
                    <div class="movie-year"><?php echo $film['ReleaseYear']; ?></div>
                    <div class="movie-rating">⭐ <?php echo $film['TMDBRating']; ?>/10</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="reviews-section">
        <div class="section-header">
            <h2 class="section-title">POPULAR REVIEWS THIS WEEK</h2>
            <a href="#" class="section-link">MORE →</a>
        </div>
        <?php foreach ($recentReviews as $review): ?>
        <div class="review-card">
            <div class="review-header">
                <span class="review-author"><?php echo htmlspecialchars($review['username']); ?></span>
                <span class="review-rating">⭐ <?php echo $review['Rating']; ?>/10</span>
            </div>
            <div class="review-text">"<?php echo htmlspecialchars(substr($review['ReviewText'], 0, 150)); ?>..."</div>
            <div class="review-likes">❤️ <?php echo rand(10, 500); ?> likes</div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="movie-section">
        <div class="section-header">
            <h2 class="section-title">POPULAR LISTS</h2>
            <a href="#" class="section-link">MORE →</a>
        </div>
        <div class="lists-grid">
            <div class="list-card">
                <div class="list-header">🎬</div>
                <div class="list-info">
                    <div class="list-title">MovieWatchlist's Top 500 Films</div>
                    <div class="list-stats">500 films • 374K likes</div>
                </div>
            </div>
            <div class="list-card">
                <div class="list-header">🚀</div>
                <div class="list-info">
                    <div class="list-title">The Best Sci-Fi Movies</div>
                    <div class="list-stats">120 films • 89K likes</div>
                </div>
            </div>
            <div class="list-card">
                <div class="list-header">🏆</div>
                <div class="list-info">
                    <div class="list-title">Oscar Winners - Best Picture</div>
                    <div class="list-stats">94 films • 156K likes</div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<style>
/* Logged In User Styles */
.user-stats-section {
    background: #1c2228;
    padding: 40px 60px;
    text-align: center;
    border-bottom: 1px solid #2c3440;
}
.user-stats-grid {
    display: flex;
    justify-content: center;
    gap: 80px;
    flex-wrap: wrap;
}
.user-stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #00e054;
    font-family: 'Libre Baskerville', serif;
}
.user-stat-label {
    color: #99aabb;
    margin-top: 8px;
    font-size: 0.85rem;
}
.hero-subtitle {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #ffffff;
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
    margin-top: 20px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
.watchlist-status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 600;
    margin-top: 8px;
}
.status-to-watch {
    background: #f5c518;
    color: #14181c;
}
.status-watching {
    background: #00c2ff;
    color: #14181c;
}
.status-watched {
    background: #00e054;
    color: #14181c;
}
</style>

<!-- JavaScript for Modals (only needed when logged out) -->
<?php if (!isset($_SESSION['user_id'])): ?>
<script>
    // Wait for DOM to fully load
    document.addEventListener('DOMContentLoaded', function() {
        // Get modal elements
        const loginModal = document.getElementById('loginModal');
        const signupModal = document.getElementById('signupModal');
        const showLoginBtn = document.getElementById('showLoginBtn');
        const showSignupBtn = document.getElementById('showSignupBtn');
        const heroGetStartedBtn = document.getElementById('heroGetStartedBtn');
        const closeLogin = document.getElementById('closeLogin');
        const closeSignup = document.getElementById('closeSignup');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');

        // Function to open signup modal
        function openSignupModal(e) {
            if (e) e.preventDefault();
            if (signupModal) {
                signupModal.style.display = 'flex';
            } else {
                window.location.href = 'register.php';
            }
        }

        // Function to open login modal
        function openLoginModal(e) {
            if (e) e.preventDefault();
            if (loginModal) {
                loginModal.style.display = 'flex';
            }
        }

        if (showLoginBtn) {
            showLoginBtn.onclick = openLoginModal;
        }

        if (showSignupBtn) {
            showSignupBtn.onclick = openSignupModal;
        }

        if (heroGetStartedBtn) {
            heroGetStartedBtn.onclick = openSignupModal;
        }

        if (closeLogin) {
            closeLogin.onclick = function() {
                loginModal.style.display = 'none';
            }
        }
        if (closeSignup) {
            closeSignup.onclick = function() {
                signupModal.style.display = 'none';
            }
        }

        if (switchToSignup) {
            switchToSignup.onclick = function(e) {
                e.preventDefault();
                loginModal.style.display = 'none';
                signupModal.style.display = 'flex';
            }
        }
        if (switchToLogin) {
            switchToLogin.onclick = function(e) {
                e.preventDefault();
                signupModal.style.display = 'none';
                loginModal.style.display = 'flex';
            }
        }

        window.onclick = function(e) {
            if (e.target == loginModal) {
                loginModal.style.display = 'none';
            }
            if (e.target == signupModal) {
                signupModal.style.display = 'none';
            }
        }
    });

    function showError(errorElement, errorTextElement, message) {
        errorTextElement.textContent = message;
        errorElement.style.display = 'flex';
        setTimeout(function() {
            errorElement.style.display = 'none';
        }, 5000);
    }

    // Handle Login Form Submission with AJAX
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            
            fetch('/movie_watchlist/login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.is_admin) {
                        window.location.href = '/movie_watchlist/admin/dashboard.php';
                    } else {
                        window.location.href = '/movie_watchlist/index.php';
                    }
                } else {
                    showError(
                        document.getElementById('loginError'),
                        document.getElementById('loginErrorText'),
                        data.error
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    // Handle Signup Form Submission with AJAX
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.onsubmit = function(e) {
            e.preventDefault();
            
            const formData = new FormData(signupForm);
            
            fetch('/movie_watchlist/register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/movie_watchlist/index.php';
                } else {
                    showError(
                        document.getElementById('signupError'),
                        document.getElementById('signupErrorText'),
                        data.error
                    );
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }
</script>
<?php endif; ?>

<!-- Make GET STARTED button trigger CREATE ACCOUNT button -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var getStartedBtn = document.getElementById('heroGetStartedBtn');
        var createAccountBtn = document.getElementById('showSignupBtn');
        
        if (getStartedBtn && createAccountBtn) {
            getStartedBtn.onclick = function(e) {
                e.preventDefault();
                createAccountBtn.click();
            };
        }
    });
</script>

<?php include 'includes/footer.php'; ?>