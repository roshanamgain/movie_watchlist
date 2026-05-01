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

// Money Heist hero image path
$heroImage = '/movie_watchlist/images/bg-image.avif';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section - Full Screen Background (Matches your CSS) -->
<div class="hero-section" style="background-image: url('<?php echo $heroImage; ?>');">
    <div class="hero-content">
        <h1>La Casa de Papel</h1>
        <p>Track films you've watched. Save those you want to see. Tell your friends what's good.</p>
        <div class="hero-buttons">
            <a href="register.php" class="btn-heist">Join the Heist — It's free!</a>
            <a href="movies/index.php" class="btn-outline-heist">Browse Films</a>
        </div>
    </div>
</div>

<div class="stats-section">
    <div class="stats-grid">
        <div class="stat">
            <div class="stat-number">3.5B+</div>
            <div class="stat-label">films watched</div>
        </div>
        <div class="stat">
            <div class="stat-number">1.2M+</div>
            <div class="stat-label">members</div>
        </div>
        <div class="stat">
            <div class="stat-number">500K+</div>
            <div class="stat-label">reviews</div>
        </div>
    </div>
</div>

<div class="movie-section">
    <div class="section-header">
        <h2 class="section-title">POPULAR THIS WEEK</h2>
        <a href="movies/index.php" class="section-link">ALL FILMS →</a>
    </div>
    <div class="film-strip">
        <?php if (count($popularFilms) > 0): ?>
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
                    <div class="movie-rating">
                        <span class="stars">★</span>
                        <span><?php echo $film['TMDBRating']; ?>/10</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No movies found. Please add movies to the database.</p>
        <?php endif; ?>
    </div>
</div>

<div class="reviews-section">
    <div class="section-header">
        <h2 class="section-title">POPULAR REVIEWS THIS WEEK</h2>
        <a href="#" class="section-link">MORE →</a>
    </div>
    <?php if (count($recentReviews) > 0): ?>
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
    <?php else: ?>
        <div class="review-card">
            <div class="review-header">
                <span class="review-author">MovieFan</span>
                <span class="review-rating">⭐ 9.5/10</span>
            </div>
            <div class="review-text">"An absolute masterpiece! Highly recommend watching this film."</div>
            <div class="review-likes">❤️ 247 likes</div>
        </div>
    <?php endif; ?>
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

<?php include 'includes/footer.php'; ?>