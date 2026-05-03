<?php
require_once '../includes/config.php';
include '../includes/header.php';

// Get filter by genre if set
$genreFilter = isset($_GET['genre']) ? $_GET['genre'] : '';
$searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// Build query based on filters
if ($genreFilter) {
    $stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE Genre = ? ORDER BY Title");
    $stmt->execute([$genreFilter]);
    $movies = $stmt->fetchAll();
    $pageTitle = $genreFilter . " Movies";
} elseif ($searchTerm) {
    $stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE Title LIKE ? ORDER BY Title");
    $stmt->execute([$searchTerm]);
    $movies = $stmt->fetchAll();
    $pageTitle = "Search Results";
} else {
    $stmt = $pdo->query("SELECT * FROM tblmovie ORDER BY Title");
    $movies = $stmt->fetchAll();
    $pageTitle = "All Movies";
}

// Get unique genres for filter
$genres = $pdo->query("SELECT DISTINCT Genre FROM tblmovie ORDER BY Genre")->fetchAll();
?>

<div class="movies-container">
    <div class="movies-header">
        <h1><?php echo $pageTitle; ?></h1>
        
        <!-- Search Bar -->
        <form method="GET" class="movies-search">
            <input type="text" name="search" placeholder="Search movies..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">🔍</button>
        </form>
    </div>
    
    <!-- Genre Filter -->
    <div class="genre-filters">
        <a href="index.php" class="genre-btn <?php echo !$genreFilter && !isset($_GET['search']) ? 'active' : ''; ?>">All</a>
        <?php foreach ($genres as $genre): ?>
            <a href="?genre=<?php echo urlencode($genre['Genre']); ?>" class="genre-btn <?php echo $genreFilter == $genre['Genre'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($genre['Genre']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Movies Grid -->
    <div class="movies-grid">
        <?php if (count($movies) > 0): ?>
            <?php foreach ($movies as $movie): ?>
            <div class="movie-card" onclick="location.href='details.php?id=<?php echo $movie['MovieID']; ?>'">
                <div class="movie-poster">
                    <?php if (!empty($movie['PosterURL'])): ?>
                        <img src="<?php echo $movie['PosterURL']; ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
                    <?php else: ?>
                        <span>🎬</span>
                    <?php endif; ?>
                </div>
                <div class="movie-info">
                    <h3 class="movie-title"><?php echo htmlspecialchars($movie['Title']); ?></h3>
                    <p class="movie-year"><?php echo $movie['ReleaseYear']; ?></p>
                    <div class="movie-rating">⭐ <?php echo $movie['TMDBRating']; ?>/10</div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="add-to-watchlist-btn" data-movie-id="<?php echo $movie['MovieID']; ?>">+ Add to Watchlist</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">No movies found.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.movies-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 0 60px;
}

.movies-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.movies-header h1 {
    font-size: 2rem;
    color: #ffffff;
}

.movies-search {
    display: flex;
    gap: 10px;
}

.movies-search input {
    padding: 10px 15px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 8px;
    color: white;
    width: 250px;
}

.movies-search button {
    padding: 10px 15px;
    background: #c41e3a;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

.genre-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
}

.genre-btn {
    padding: 8px 16px;
    background: #2c3440;
    border-radius: 20px;
    text-decoration: none;
    color: #99aabb;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.genre-btn:hover,
.genre-btn.active {
    background: #c41e3a;
    color: white;
}

.movies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 25px;
}

.add-to-watchlist-btn {
    width: 100%;
    margin-top: 10px;
    padding: 6px;
    background: #00e054;
    border: none;
    border-radius: 5px;
    color: #14181c;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.7rem;
}

.add-to-watchlist-btn:hover {
    background: #00c946;
}

.no-results {
    text-align: center;
    color: #99aabb;
    padding: 40px;
}
</style>

<?php include '../includes/footer.php'; ?>