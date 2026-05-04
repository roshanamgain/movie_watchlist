<?php
require_once '../includes/config.php';
include '../includes/header.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

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
            <button type="submit">🔍 Search</button>
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
                    <p class="movie-genre"><?php echo htmlspecialchars($movie['Genre']); ?></p>
                    <div class="movie-rating">⭐ <?php echo $movie['TMDBRating']; ?>/10</div>
                    <?php if ($isLoggedIn): ?>
                        <button class="watchlist-add" data-movie-id="<?php echo $movie['MovieID']; ?>">+ Add to Watchlist</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🎬</div>
                <h3>No movies found</h3>
                <p>Try a different search or filter.</p>
                <?php if (!$isLoggedIn): ?>
                    <a href="../register.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #c41e3a; color: white; text-decoration: none; border-radius: 30px;">Sign up to add movies</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- For Logged Out Users - Show CTA Section -->
    <?php if (!$isLoggedIn): ?>
       <div style="margin-top: 50px; background: linear-gradient(135deg, #1c2228, #242c34); border-radius: 24px; padding: 50px; text-align: center; border: 1px solid #c41e3a;">
        <h2 style="font-size: 1.8rem; margin-bottom: 15px; color: white;">🎬 Can't find your favorite movie?</h2>
        <p style="color: rgba(255,255,255,0.9); margin-bottom: 25px;">Create an account and add new movies to our collection!</p>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-bottom: 30px;">
            <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 30px; color: white;">✅ Track watched movies</span>
            <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 30px; color: white;">⭐ Rate and review</span>
            <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 30px; color: white;">📋 Create watchlist</span>
            <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 30px; color: white;">➕ Add new movies</span>
        </div>
        <a href="javascript:void(0)" onclick="openSignupFromMovies()" style="display: inline-block; padding: 14px 40px; background: #c41e3a; color: white; text-decoration: none; border: none; border-radius: 50px; font-weight: 700; font-size: 1.1rem; cursor: pointer;">Get Started — It's Free!</a>
    </div>
    <?php endif; ?>
    
    <!-- For Logged In Users - Show Add Movie CTA Section at Bottom -->
    <?php if ($isLoggedIn): ?>
    <div style="margin-top: 50px; background: linear-gradient(135deg, #1c2228, #242c34); border-radius: 24px; padding: 50px; text-align: center; border: 1px solid #c41e3a;">
        <h2 style="font-size: 1.8rem; margin-bottom: 15px; color: white;">🎬 Can't find your favorite movie?</h2>
        <p style="color: #99aabb; margin-bottom: 25px;">Add it to our collection and share it with other film lovers!</p>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-bottom: 30px;">
        </div>
        <button onclick="openAddMovieModal()" style="display: inline-block; padding: 14px 40px; background: #c41e3a; color: white; border: none; border-radius: 50px; font-weight: 700; font-size: 1.1rem; cursor: pointer;">➕ Add New Movie</button>
    </div>
    <?php endif; ?>
</div>

<!-- Add Movie Modal (for ALL logged-in users) -->
<div id="addMovieModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: #1c2228; border-radius: 16px; padding: 30px; max-width: 500px; width: 90%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: white; margin: 0;">➕ Add New Movie</h3>
            <span onclick="closeAddMovieModal()" style="color: #99aabb; font-size: 28px; cursor: pointer;">&times;</span>
        </div>
        <form id="userAddMovieForm">
            <input type="text" name="title" placeholder="Movie Title" required style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;">
            <input type="text" name="genre" placeholder="Genre" required style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;">
            <input type="number" name="release_year" placeholder="Release Year" required style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;">
            <input type="text" name="poster_url" placeholder="Poster URL (optional)" style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;">
            <input type="text" name="tmdb_rating" placeholder="TMDB Rating (0-10)" step="0.1" style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;">
            <textarea name="description" placeholder="Movie Description" rows="3" style="width: 100%; padding: 10px; margin-bottom: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white;"></textarea>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="submit" style="background: #c41e3a; color: white; padding: 8px 20px; border: none; border-radius: 30px; cursor: pointer;">Add Movie</button>
                <button type="button" onclick="closeAddMovieModal()" style="background: #2c3440; color: white; padding: 8px 20px; border: none; border-radius: 30px; cursor: pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.movies-container {
    max-width: 1200px;
    margin: 100px auto 60px;
    padding: 0 24px;
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
    color: white;
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
    grid-column: 1 / -1;
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

@media (max-width: 768px) {
    .movies-container {
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

// Add Movie Modal functions
function openAddMovieModal() {
    document.getElementById('addMovieModal').style.display = 'flex';
}

function closeAddMovieModal() {
    document.getElementById('addMovieModal').style.display = 'none';
}

// Open signup modal for non-logged users
function openSignupFromMovies() {
    var signupModal = document.getElementById('signupModal');
    if (signupModal) {
        signupModal.style.display = 'flex';
    } else {
        window.location.href = '../register.php';
    }
}

<?php if ($isLoggedIn): ?>
// Close modal when clicking outside
window.onclick = function(e) {
    var modal = document.getElementById('addMovieModal');
    if (e.target == modal) {
        modal.style.display = 'none';
    }
}

// User Add Movie Form Submission
document.getElementById('userAddMovieForm').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'add_movie');
    
    fetch('user_add_movie.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Movie added successfully!');
            location.reload();
        } else {
            alert(data.error || 'Failed to add movie');
        }
    })
    .catch(error => console.error('Error:', error));
    return false;
};
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>