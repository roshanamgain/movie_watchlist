<?php
require_once '../includes/config.php';
include '../includes/header.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Get popular movies for public view
$stmt = $pdo->query("SELECT * FROM tblmovie ORDER BY TMDBRating DESC LIMIT 8");
$popularMovies = $stmt->fetchAll();

// Get recent watchlist activity (for public view)
$stmt = $pdo->query("
    SELECT w.*, u.FullName, m.Title, m.ReleaseYear, m.PosterURL
    FROM tblwatchlist w 
    JOIN tbluser u ON w.UserID = u.UserID 
    JOIN tblmovie m ON w.MovieID = m.MovieID 
    WHERE u.IsActive = 1
    ORDER BY w.AddedDate DESC LIMIT 10
");
$recentActivity = $stmt->fetchAll();
?>

<?php if ($isLoggedIn): ?>
    <!-- ============================================ -->
    <!-- LOGGED IN USER VIEW - Personal Watchlist      -->
    <!-- ============================================ -->
    <?php
    $userId = $_SESSION['user_id'];
    $success = '';
    
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
        $watchlistId = $_POST['watchlist_id'];
        $newStatus = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE tblwatchlist SET Status = ? WHERE WatchlistID = ? AND UserID = ?");
        $stmt->execute([$newStatus, $watchlistId, $userId]);
        $success = 'Status updated successfully!';
    }
    
    // Handle remove from watchlist
    if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
        $removeId = $_GET['remove'];
        $stmt = $pdo->prepare("DELETE FROM tblwatchlist WHERE WatchlistID = ? AND UserID = ?");
        $stmt->execute([$removeId, $userId]);
        $success = 'Movie removed from your watchlist!';
        header('Location: index.php');
        exit();
    }
    
    // Get user's watchlist
    $stmt = $pdo->prepare("
        SELECT w.*, m.Title, m.ReleaseYear, m.PosterURL, m.TMDBRating, m.Genre
        FROM tblwatchlist w 
        JOIN tblmovie m ON w.MovieID = m.MovieID 
        WHERE w.UserID = ? 
        ORDER BY 
            CASE w.Status 
                WHEN 'To Watch' THEN 1 
                WHEN 'Watching' THEN 2 
                WHEN 'Watched' THEN 3 
            END,
            w.AddedDate DESC
    ");
    $stmt->execute([$userId]);
    $watchlist = $stmt->fetchAll();
    
    // Count by status
    $toWatchCount = 0;
    $watchingCount = 0;
    $watchedCount = 0;
    foreach ($watchlist as $item) {
        if ($item['Status'] == 'To Watch') $toWatchCount++;
        elseif ($item['Status'] == 'Watching') $watchingCount++;
        elseif ($item['Status'] == 'Watched') $watchedCount++;
    }
    ?>
    
    <div class="watchlist-container">
        <div class="watchlist-header">
            <h1>My Watchlist</h1>
            <div class="watchlist-stats">
                <span class="stat-badge to-watch">📌 To Watch: <?php echo $toWatchCount; ?></span>
                <span class="stat-badge watching">▶️ Watching: <?php echo $watchingCount; ?></span>
                <span class="stat-badge watched">✅ Watched: <?php echo $watchedCount; ?></span>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (count($watchlist) > 0): ?>
            <div class="watchlist-grid">
                <?php foreach ($watchlist as $item): ?>
                    <div class="watchlist-item">
                        <div class="watchlist-poster" onclick="location.href='../movies/details.php?id=<?php echo $item['MovieID']; ?>'">
                            <?php if (!empty($item['PosterURL'])): ?>
                                <img src="<?php echo $item['PosterURL']; ?>" alt="<?php echo htmlspecialchars($item['Title']); ?>">
                            <?php else: ?>
                                <span>🎬</span>
                            <?php endif; ?>
                        </div>
                        <div class="watchlist-info">
                            <h3 onclick="location.href='../movies/details.php?id=<?php echo $item['MovieID']; ?>'"><?php echo htmlspecialchars($item['Title']); ?> (<?php echo $item['ReleaseYear']; ?>)</h3>
                            <p class="movie-genre">🎭 <?php echo htmlspecialchars($item['Genre']); ?></p>
                            <div class="watchlist-rating">⭐ <?php echo $item['TMDBRating']; ?>/10</div>
                            <div class="watchlist-actions">
                                <form method="POST" class="status-form" style="display: inline;">
                                    <input type="hidden" name="watchlist_id" value="<?php echo $item['WatchlistID']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="To Watch" <?php echo $item['Status'] == 'To Watch' ? 'selected' : ''; ?>>📌 To Watch</option>
                                        <option value="Watching" <?php echo $item['Status'] == 'Watching' ? 'selected' : ''; ?>>▶️ Watching</option>
                                        <option value="Watched" <?php echo $item['Status'] == 'Watched' ? 'selected' : ''; ?>>✅ Watched</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                <a href="?remove=<?php echo $item['WatchlistID']; ?>" class="remove-btn" onclick="return confirm('Remove this movie from your watchlist?')">🗑️ Remove</a>
                            </div>
                            <div class="added-date">Added on: <?php echo date('F j, Y', strtotime($item['AddedDate'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-watchlist">
                <div class="empty-icon">🎬</div>
                <h3>Your watchlist is empty</h3>
                <p>Start adding movies to your watchlist and keep track of what you want to watch!</p>
                <a href="../movies/index.php" class="browse-btn">Browse Movies</a>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ============================================ -->
    <!-- LOGGED OUT USER VIEW - Public Watchlist      -->
    <!-- ============================================ -->
    
    <div class="public-watchlist-container">
        <!-- Hero Section -->
        <div class="public-hero">
            <h1>📋 Track Your Movie Journey</h1>
            <p>Create your personal watchlist, rate movies you've watched, and discover your next favorite film.</p>
            <a href="javascript:void(0)" class="cta-btn" id="heroGetStartedBtn">Get Started — It's Free!</a>
        </div>

        <!-- Popular Movies Section -->
        <div class="popular-section">
            <h2>🔥 Popular Movies</h2>
            <div class="movies-grid">
                <?php foreach ($popularMovies as $movie): ?>
                    <div class="movie-card" onclick="location.href='../movies/details.php?id=<?php echo $movie['MovieID']; ?>'">
                        <div class="movie-poster">
                            <?php if (!empty($movie['PosterURL'])): ?>
                                <img src="<?php echo $movie['PosterURL']; ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
                            <?php else: ?>
                                <span>🎬</span>
                            <?php endif; ?>
                        </div>
                        <div class="movie-info">
                            <h4><?php echo htmlspecialchars($movie['Title']); ?></h4>
                            <p><?php echo $movie['ReleaseYear']; ?></p>
                            <span class="rating">⭐ <?php echo $movie['TMDBRating']; ?>/10</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="activity-section">
            <h2>📊 Recent Activity</h2>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-avatar">👤</div>
                        <div class="activity-details">
                            <strong><?php echo htmlspecialchars($activity['FullName']); ?></strong> added 
                            <span class="movie-title"><?php echo htmlspecialchars($activity['Title']); ?></span> 
                            to their watchlist
                            <div class="activity-time"><?php echo date('M d, Y', strtotime($activity['AddedDate'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    <style>
        .public-watchlist-container {
            max-width: 1200px;
            margin: 100px auto 60px;
            padding: 0 24px;
        }
        
        .public-hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #1c2228, #14181c);
            border-radius: 24px;
            margin-bottom: 50px;
        }
        
        .public-hero h1 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .public-hero p {
            font-size: 1.1rem;
            color: #99aabb;
            margin-bottom: 25px;
        }
        
        .cta-btn {
            display: inline-block;
            padding: 12px 32px;
            background: #c41e3a;
            color: white;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .cta-btn:hover {
            background: #a01830;
            transform: translateY(-2px);
        }
        
        .popular-section h2, .activity-section h2 {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: white;
        }
        
        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
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
            height: 250px;
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
            padding: 12px;
        }
        
        .movie-info h4 {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: white;
        }
        
        .movie-info p {
            font-size: 0.7rem;
            color: #556677;
            margin-bottom: 6px;
        }
        
        .rating {
            font-size: 0.7rem;
            color: #f5c518;
        }
        
        .activity-list {
            background: #1c2228;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 50px;
        }
        
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px 20px;
            border-bottom: 1px solid #2c3440;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-avatar {
            width: 40px;
            height: 40px;
            background: #c41e3a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .activity-details {
            flex: 1;
            color: #99aabb;
        }
        
        .activity-details strong {
            color: white;
        }
        
        .movie-title {
            color: #c41e3a;
            font-weight: 600;
        }
        
        .activity-time {
            font-size: 0.7rem;
            color: #556677;
            margin-top: 4px;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #c41e3a, #a01830);
            border-radius: 24px;
            padding: 50px;
            text-align: center;
            margin-top: 20px;
        }
        
        .cta-content h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: white;
        }
        
        .cta-content p {
            color: rgba(255,255,255,0.9);
            margin-bottom: 25px;
        }
        
        .cta-features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .cta-features span {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            color: white;
        }
        
        .cta-btn-large {
            display: inline-block;
            padding: 14px 40px;
            background: white;
            color: #c41e3a;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .cta-btn-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .public-watchlist-container {
                margin-top: 80px;
                padding: 0 16px;
            }
            
            .public-hero h1 {
                font-size: 1.5rem;
            }
            
            .movies-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }
            
            .movie-poster {
                height: 200px;
            }
            
            .cta-section {
                padding: 30px 20px;
            }
            
            .cta-content h2 {
                font-size: 1.3rem;
            }
            
            .cta-features {
                gap: 10px;
            }
            
            .cta-features span {
                font-size: 0.7rem;
            }
        }
    </style>

    <!-- Hide any error boxes on public page -->
    <style>
        .modal-error {
            display: none !important;
        }
    </style>

    <!-- Make GET STARTED buttons trigger CREATE ACCOUNT button -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var getStartedBtn = document.getElementById('heroGetStartedBtn');
            var getStartedBtn2 = document.getElementById('heroGetStartedBtn2');
            var createAccountBtn = document.getElementById('showSignupBtn');
            
            if (getStartedBtn && createAccountBtn) {
                getStartedBtn.onclick = function(e) {
                    e.preventDefault();
                    createAccountBtn.click();
                };
            }
            if (getStartedBtn2 && createAccountBtn) {
                getStartedBtn2.onclick = function(e) {
                    e.preventDefault();
                    createAccountBtn.click();
                };
            }
        });
    </script>

<?php endif; ?>

<style>
/* Common Styles for both views */
.watchlist-container {
    max-width: 1000px;
    margin: 100px auto 60px;
    padding: 0 24px;
}

.watchlist-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.watchlist-header h1 {
    font-size: 2rem;
    color: white;
}

.watchlist-stats {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.stat-badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stat-badge.to-watch {
    background: rgba(245, 197, 24, 0.2);
    color: #f5c518;
    border: 1px solid #f5c518;
}

.stat-badge.watching {
    background: rgba(0, 194, 255, 0.2);
    color: #00c2ff;
    border: 1px solid #00c2ff;
}

.stat-badge.watched {
    background: rgba(0, 224, 84, 0.2);
    color: #00e054;
    border: 1px solid #00e054;
}

.alert-success {
    background: rgba(0, 224, 84, 0.15);
    border: 1px solid #00e054;
    color: #00e054;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.watchlist-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.watchlist-item {
    display: flex;
    gap: 20px;
    background: #1c2228;
    border-radius: 16px;
    padding: 20px;
    transition: transform 0.2s;
    border: 1px solid #2c3440;
}

.watchlist-item:hover {
    transform: translateY(-2px);
    border-color: #c41e3a;
}

.watchlist-poster {
    width: 100px;
    height: 150px;
    background: #2c3440;
    border-radius: 12px;
    cursor: pointer;
    overflow: hidden;
    flex-shrink: 0;
}

.watchlist-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.watchlist-poster span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 2.5rem;
}

.watchlist-info {
    flex: 1;
}

.watchlist-info h3 {
    font-size: 1.2rem;
    margin-bottom: 5px;
    cursor: pointer;
    color: white;
}

.watchlist-info h3:hover {
    color: #c41e3a;
}

.movie-genre {
    color: #99aabb;
    font-size: 0.85rem;
    margin-bottom: 8px;
}

.watchlist-rating {
    color: #f5c518;
    margin-bottom: 12px;
}

.watchlist-actions {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.status-select {
    padding: 8px 16px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 30px;
    color: white;
    cursor: pointer;
    font-size: 0.8rem;
}

.remove-btn {
    color: #c41e3a;
    text-decoration: none;
    font-size: 0.8rem;
    padding: 8px 16px;
    background: rgba(196, 30, 58, 0.1);
    border-radius: 30px;
    transition: all 0.2s;
}

.remove-btn:hover {
    background: #c41e3a;
    color: white;
}

.added-date {
    font-size: 0.7rem;
    color: #556677;
}

.empty-watchlist {
    text-align: center;
    padding: 60px;
    background: #1c2228;
    border-radius: 20px;
    border: 1px solid #2c3440;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-watchlist h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: white;
}

.empty-watchlist p {
    color: #99aabb;
    margin-bottom: 25px;
}

.browse-btn {
    display: inline-block;
    padding: 10px 28px;
    background: #c41e3a;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    transition: all 0.2s;
}

.browse-btn:hover {
    background: #a01830;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .watchlist-container {
        margin-top: 80px;
        padding: 0 16px;
    }
    
    .watchlist-item {
        flex-direction: column;
    }
    
    .watchlist-poster {
        width: 100%;
        height: 200px;
    }
    
    .watchlist-header {
        flex-direction: column;
        text-align: center;
    }
    
    .watchlist-stats {
        justify-content: center;
    }
}
</style>

<!-- Hide any error boxes -->
<style>
    .modal-error {
        display: none !important;
    }
</style>

<?php include '../includes/footer.php'; ?>

<style>
    .modal-error {
        display: none !important;
    }
</style>