<?php
require_once '../includes/config.php';
include '../includes/header.php';

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
$genreFilter = isset($_GET['genre']) ? $_GET['genre'] : '';

// Build query based on search term and genre
$query = "SELECT * FROM tblmovie WHERE 1=1";
$params = [];

if (!empty($searchTerm)) {
    $query .= " AND Title LIKE ?";
    $params[] = '%' . $searchTerm . '%';
}

if (!empty($genreFilter)) {
    $query .= " AND Genre = ?";
    $params[] = $genreFilter;
}

$query .= " ORDER BY Title";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Get all genres for filter
$genres = $pdo->query("SELECT DISTINCT Genre FROM tblmovie ORDER BY Genre")->fetchAll();
?>

<div class="search-container">
    <div class="search-header">
        <h1>Search Movies</h1>
        <form class="search-form" method="GET" action="search.php">
            <div class="search-input-group">
                <input type="text" name="q" placeholder="Search by title..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit">🔍 Search</button>
            </div>
        </form>
    </div>

    <!-- Genre Filter -->
    <div class="genre-filters">
        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>" class="genre-btn <?php echo empty($genreFilter) ? 'active' : ''; ?>">All Genres</a>
        <?php foreach ($genres as $genre): ?>
            <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&genre=<?php echo urlencode($genre['Genre']); ?>" 
               class="genre-btn <?php echo $genreFilter == $genre['Genre'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($genre['Genre']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Results -->
    <div class="search-results">
        <div class="results-header">
            <h2>
                <?php if (!empty($searchTerm)): ?>
                    Search results for: "<?php echo htmlspecialchars($searchTerm); ?>"
                <?php elseif (!empty($genreFilter)): ?>
                    <?php echo htmlspecialchars($genreFilter); ?> Movies
                <?php else: ?>
                    All Movies
                <?php endif; ?>
            </h2>
            <span class="results-count"><?php echo count($results); ?> movie<?php echo count($results) != 1 ? 's' : ''; ?> found</span>
        </div>

        <?php if (count($results) > 0): ?>
            <div class="movies-grid">
                <?php foreach ($results as $movie): ?>
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
                            <p class="movie-genre"><?php echo htmlspecialchars($movie['Genre']); ?></p>
                            <div class="movie-rating">⭐ <?php echo $movie['TMDBRating']; ?>/10</div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="watchlist-add" data-movie-id="<?php echo $movie['MovieID']; ?>">+ Add to Watchlist</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>No movies found</h3>
                <p>We couldn't find any movies matching "<?php echo htmlspecialchars($searchTerm); ?>"</p>
                <a href="index.php" class="back-btn">Browse All Movies</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.search-container {
    max-width: 1200px;
    margin: 100px auto 60px;
    padding: 0 24px;
}

.search-header {
    text-align: center;
    margin-bottom: 30px;
}

.search-header h1 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: white;
}

.search-input-group {
    display: flex;
    justify-content: center;
    gap: 10px;
    max-width: 500px;
    margin: 0 auto;
}

.search-input-group input {
    flex: 1;
    padding: 12px 18px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 30px;
    color: white;
    font-size: 1rem;
}

.search-input-group input:focus {
    outline: none;
    border-color: #c41e3a;
}

.search-input-group button {
    padding: 12px 24px;
    background: #c41e3a;
    border: none;
    border-radius: 30px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.search-input-group button:hover {
    background: #a01830;
}

.genre-filters {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 40px;
}

.genre-btn {
    padding: 6px 16px;
    background: #2c3440;
    border-radius: 30px;
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

.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.results-header h2 {
    font-size: 1.3rem;
    color: white;
}

.results-count {
    color: #99aabb;
    font-size: 0.85rem;
}

.movies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 25px;
}

.movie-card {
    background: #1c2228;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
    border: 1px solid #2c3440;
}

.movie-card:hover {
    transform: translateY(-4px);
    border-color: #c41e3a;
}

.movie-poster {
    width: 100%;
    height: 280px;
    background: #2c3440;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.movie-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.movie-poster span {
    font-size: 3rem;
}

.movie-info {
    padding: 15px;
}

.movie-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: white;
}

.movie-year {
    font-size: 0.75rem;
    color: #556677;
    margin-bottom: 5px;
}

.movie-genre {
    font-size: 0.7rem;
    color: #c41e3a;
    margin-bottom: 8px;
}

.movie-rating {
    color: #f5c518;
    font-size: 0.8rem;
    margin-bottom: 10px;
}

.watchlist-add {
    width: 100%;
    padding: 6px;
    background: #c41e3a;
    border: none;
    border-radius: 20px;
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.watchlist-add:hover {
    background: #a01830;
}

.no-results {
    text-align: center;
    padding: 60px;
    background: #1c2228;
    border-radius: 20px;
}

.no-results-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: white;
}

.no-results p {
    color: #99aabb;
    margin-bottom: 25px;
}

.back-btn {
    display: inline-block;
    padding: 10px 28px;
    background: #c41e3a;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .search-container {
        margin-top: 80px;
        padding: 0 16px;
    }
    
    .movies-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .movie-poster {
        height: 220px;
    }
}
</style>

<script>
// Add to watchlist functionality
document.querySelectorAll('.watchlist-add').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const movieId = this.dataset.movieId;
        
        fetch('../watchlist/add_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'movie_id=' + movieId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.textContent = '✓ Added';
                this.style.background = '#00e054';
                this.style.color = '#14181c';
                setTimeout(() => {
                    this.textContent = '+ Add to Watchlist';
                    this.style.background = '#c41e3a';
                    this.style.color = 'white';
                }, 2000);
            } else {
                alert(data.error || 'Failed to add');
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>

<?php include '../includes/footer.php'; ?>