<?php
/**
 * Search/Find Movies to Add to Watchlist
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file allows users to search for movies by title or genre
 * and add them directly to their watchlist
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];
$search_term = '';
$results = [];

// Process search from POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['q'])) {
    $search_term = sanitizeText($_POST['search'] ?? $_GET['q'] ?? '');
    
    if (!empty($search_term) && strlen($search_term) >= 2) {
        try {
            // Get user's current watchlist movies to mark as "already added"
            $watchlistStmt = $conn->prepare("SELECT MovieID FROM tblwatchlist WHERE UserID = :uid");
            $watchlistStmt->execute([':uid' => $user_id]);
            $existingMovies = $watchlistStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Search in movie database
            $stmt = $conn->prepare("
                SELECT m.*, 
                    (SELECT Status FROM tblwatchlist WHERE UserID = :uid AND MovieID = m.MovieID LIMIT 1) as InWatchlistStatus
                FROM tblmovie m
                WHERE m.Title LIKE :search OR m.Genre LIKE :search
                ORDER BY 
                    CASE WHEN m.Title LIKE :exact THEN 1 ELSE 2 END,
                    m.Title
                LIMIT 30
            ");
            $stmt->execute([
                ':uid' => $user_id,
                ':search' => "%$search_term%",
                ':exact' => "$search_term%"
            ]);
            $results = $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $error = "Search error: " . $e->getMessage();
        }
    } elseif (!empty($search_term) && strlen($search_term) < 2) {
        $error = "Please enter at least 2 characters to search.";
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🔍 Find Movies</h1>
        <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <!-- Search Form -->
    <div class="search-box">
        <form method="POST" action="" class="search-form">
            <div class="search-input-group">
                <input type="text" name="search" 
                       placeholder="Search by movie title or genre... (e.g., Inception, Sci-Fi)"
                       value="<?php echo htmlspecialchars($search_term); ?>" 
                       required>
                <button type="submit" class="btn btn-primary">🔍 Search</button>
            </div>
        </form>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Search Results -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($search_term) && strlen($search_term) >= 2): ?>
        <div class="search-results">
            <h2>Search Results for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</h2>
            <p class="results-count">Found <?php echo count($results); ?> movies</p>
            
            <?php if (empty($results)): ?>
                <div class="alert alert-info">
                    📭 No movies found matching your search. 
                    <br>Try different keywords or check the <a href="../movies.php">movie catalog</a>.
                </div>
            <?php else: ?>
                <div class="movies-grid">
                    <?php foreach ($results as $movie): ?>
                        <div class="movie-card">
                            <div class="movie-card-header">
                                <h3><?php echo htmlspecialchars($movie['Title']); ?></h3>
                                <span class="year">(<?php echo $movie['ReleaseYear']; ?>)</span>
                            </div>
                            <div class="movie-card-body">
                                <p><strong>Genre:</strong> <?php echo $movie['Genre']; ?></p>
                                <p><strong>TMDB Rating:</strong> <?php echo $movie['TMDBRating'] ?? 'N/A'; ?>/10</p>
                                <?php if (!empty($movie['Description'])): ?>
                                    <p class="description">
                                        <?php echo htmlspecialchars(substr($movie['Description'], 0, 100)); ?>...
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="movie-card-footer">
                                <?php if ($movie['InWatchlistStatus']): ?>
                                    <div class="already-in-watchlist">
                                        ✅ Already in your watchlist 
                                        (Status: <?php echo $movie['InWatchlistStatus']; ?>)
                                        <br>
                                        <a href="my_watchlist.php" class="btn-small">📋 View My Watchlist</a>
                                    </div>
                                <?php else: ?>
                                    <a href="add.php?movie_id=<?php echo $movie['MovieID']; ?>" class="btn btn-primary">➕ Add to Watchlist</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Popular Suggestions -->
    <div class="suggestions">
        <h3>Popular Genres to Explore:</h3>
        <div class="genre-tags">
            <a href="?q=Action" class="genre-tag">🎬 Action</a>
            <a href="?q=Sci-Fi" class="genre-tag">🚀 Sci-Fi</a>
            <a href="?q=Crime" class="genre-tag">🔫 Crime</a>
            <a href="?q=Thriller" class="genre-tag">😱 Thriller</a>
            <a href="?q=Comedy" class="genre-tag">😂 Comedy</a>
            <a href="?q=Drama" class="genre-tag">🎭 Drama</a>
        </div>
    </div>
</div>

<style>
.search-box {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 30px;
}
.search-form {
    max-width: 600px;
    margin: 0 auto;
}
.search-input-group {
    display: flex;
    gap: 10px;
}
.search-input-group input {
    flex: 1;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
}
.search-input-group input:focus {
    outline: none;
    border-color: #007bff;
}
.results-count {
    color: #666;
    margin-bottom: 20px;
}
.movies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.movie-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.movie-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.movie-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 8px;
}
.movie-card-header h3 {
    margin: 0;
    color: #333;
}
.year {
    color: #666;
    font-size: 14px;
}
.movie-card-body p {
    margin: 8px 0;
    font-size: 14px;
    color: #555;
}
.description {
    color: #666;
    font-style: italic;
    font-size: 13px;
}
.movie-card-footer {
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}
.already-in-watchlist {
    background: #d4edda;
    padding: 10px;
    border-radius: 5px;
    font-size: 13px;
    color: #155724;
    text-align: center;
}
.suggestions {
    margin-top: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}
.genre-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
.genre-tag {
    background: #007bff;
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 14px;
    transition: background 0.2s;
}
.genre-tag:hover {
    background: #0056b3;
}
.btn-small {
    display: inline-block;
    padding: 5px 12px;
    background: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 12px;
    margin-top: 8px;
}
</style>

<?php include '../includes/footer.php'; ?>