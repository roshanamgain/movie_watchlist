<?php
/**
 * Filter Watchlist by Genre, Status, Priority, Rating
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file allows users to filter their watchlist using multiple criteria:
 * - Filter by movie genre
 * - Filter by watch status (To Watch, Watching Now, Watched)
 * - Filter by priority (High, Medium, Low)
 * - Filter by minimum user rating
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];

// Get filter criteria from URL parameters (GET request)
$genre = sanitizeText($_GET['genre'] ?? '');
$status = validateStatus($_GET['status'] ?? '');
$priority = validatePriority($_GET['priority'] ?? '');
$min_rating = filter_var($_GET['min_rating'] ?? '', FILTER_VALIDATE_INT);

// Build SQL query dynamically based on filters
$conditions = ["w.UserID = :uid"];
$params = [':uid' => $user_id];

// Add conditions only if filter is applied
if (!empty($genre)) {
    $conditions[] = "m.Genre = :genre";
    $params[':genre'] = $genre;
}
if ($status) {
    $conditions[] = "w.Status = :status";
    $params[':status'] = $status;
}
if ($priority) {
    $conditions[] = "w.Priority = :priority";
    $params[':priority'] = $priority;
}
if ($min_rating && $min_rating >= 1 && $min_rating <= 10) {
    $conditions[] = "w.PersonalRating >= :min_rating";
    $params[':min_rating'] = $min_rating;
}

$whereClause = implode(" AND ", $conditions);

try {
    // Query to get filtered watchlist
    $sql = "
        SELECT w.*, m.Title, m.Genre, m.ReleaseYear, m.TMDBRating
        FROM tblwatchlist w
        JOIN tblmovie m ON w.MovieID = m.MovieID
        WHERE $whereClause
        ORDER BY 
            CASE w.Priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 END,
            w.AddedDate DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    // Get distinct genres from all movies for filter dropdown
    $genreStmt = $conn->prepare("SELECT DISTINCT Genre FROM tblmovie ORDER BY Genre");
    $genreStmt->execute();
    $genres = $genreStmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error loading watchlist: " . $e->getMessage();
    $results = [];
    $genres = [];
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>🎯 Filter Your Watchlist</h1>
        <a href="my_watchlist.php" class="btn btn-secondary">← View Complete Watchlist</a>
    </div>
    
    <!-- Filter Panel -->
    <div class="filter-panel">
        <h3>Apply Filters</h3>
        <form method="GET" action="" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>🎭 Genre</label>
                    <select name="genre">
                        <option value="">All Genres</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?php echo htmlspecialchars($g['Genre']); ?>" 
                                    <?php echo $genre == $g['Genre'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['Genre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>📋 Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="To Watch" <?php echo $status == 'To Watch' ? 'selected' : ''; ?>>📋 To Watch</option>
                        <option value="Watching Now" <?php echo $status == 'Watching Now' ? 'selected' : ''; ?>>▶️ Watching Now</option>
                        <option value="Watched" <?php echo $status == 'Watched' ? 'selected' : ''; ?>>✅ Watched</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>⚡ Priority</label>
                    <select name="priority">
                        <option value="">All Priority</option>
                        <option value="High" <?php echo $priority == 'High' ? 'selected' : ''; ?>>🔴 High</option>
                        <option value="Medium" <?php echo $priority == 'Medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                        <option value="Low" <?php echo $priority == 'Low' ? 'selected' : ''; ?>>🟢 Low</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>⭐ Min Rating</label>
                    <input type="number" name="min_rating" placeholder="1-10" min="1" max="10" step="1"
                           value="<?php echo $min_rating; ?>">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">🔍 Apply Filters</button>
                <a href="filter.php" class="btn btn-secondary">🔄 Reset All Filters</a>
            </div>
        </form>
    </div>
    
    <!-- Results Section -->
    <div class="results-header">
        <h2>Filter Results (<?php echo count($results); ?> movies found)</h2>
        <?php if (!empty($genre) || $status || $priority || $min_rating): ?>
            <p class="active-filters">
                <strong>Active Filters:</strong>
                <?php if(!empty($genre)) echo " Genre: $genre |"; ?>
                <?php if($status) echo " Status: $status |"; ?>
                <?php if($priority) echo " Priority: $priority |"; ?>
                <?php if($min_rating) echo " Min Rating: $min_rating/10"; ?>
            </p>
        <?php endif; ?>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (empty($results)): ?>
        <div class="alert alert-info">
            📭 No movies match your filters. 
            <a href="add.php">Add some movies to your watchlist!</a> or 
            <a href="filter.php">Clear all filters</a>
        </div>
    <?php else: ?>
        <div class="watchlist-grid">
            <?php foreach ($results as $movie): ?>
                <div class="movie-card">
                    <div class="movie-card-header">
                        <h3><?php echo htmlspecialchars($movie['Title']); ?></h3>
                        <span class="year">(<?php echo $movie['ReleaseYear']; ?>)</span>
                    </div>
                    <div class="movie-card-details">
                        <p><strong>Genre:</strong> <?php echo $movie['Genre']; ?></p>
                        <p><strong>TMDB Rating:</strong> <?php echo $movie['TMDBRating'] ?? 'N/A'; ?>/10</p>
                        <p><strong>Your Rating:</strong> <?php echo $movie['PersonalRating'] ?: 'Not rated yet'; ?>/10</p>
                        <?php if (!empty($movie['PersonalNotes'])): ?>
                            <p><strong>Notes:</strong> <?php echo htmlspecialchars(substr($movie['PersonalNotes'], 0, 50)); ?>...</p>
                        <?php endif; ?>
                    </div>
                    <div class="movie-card-badges">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $movie['Status'])); ?>">
                            <?php echo $movie['Status']; ?>
                        </span>
                        <span class="priority-badge priority-<?php echo strtolower($movie['Priority']); ?>">
                            <?php echo $movie['Priority']; ?> Priority
                        </span>
                    </div>
                    <div class="movie-card-actions">
                        <a href="edit.php?id=<?php echo $movie['WatchlistID']; ?>" class="btn-small">✏️ Edit</a>
                        <a href="update_status.php?id=<?php echo $movie['WatchlistID']; ?>" class="btn-small">📝 Status</a>
                        <a href="remove.php?id=<?php echo $movie['WatchlistID']; ?>" class="btn-small btn-danger" 
                           onclick="return confirm('Remove this movie from your watchlist?')">🗑️ Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.filter-panel {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    border: 1px solid #ddd;
}
.filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}
.filter-group {
    flex: 1;
    min-width: 150px;
}
.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}
.filter-group select, .filter-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}
.results-header {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}
.active-filters {
    background: #e9ecef;
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 14px;
}
.watchlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.movie-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    transition: box-shadow 0.3s;
}
.movie-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.movie-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
}
.movie-card-header h3 {
    margin: 0;
    font-size: 18px;
}
.year {
    color: #666;
    font-size: 14px;
}
.movie-card-details p {
    margin: 8px 0;
    font-size: 14px;
}
.movie-card-badges {
    display: flex;
    gap: 10px;
    margin: 12px 0;
}
.status-badge, .priority-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
.status-to-watch { background: #ffc107; color: #333; }
.status-watching-now { background: #17a2b8; color: white; }
.status-watched { background: #28a745; color: white; }
.priority-high { background: #dc3545; color: white; }
.priority-medium { background: #ffc107; color: #333; }
.priority-low { background: #28a745; color: white; }
.movie-card-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}
.btn-small {
    padding: 5px 12px;
    font-size: 12px;
    border-radius: 5px;
    text-decoration: none;
    background: #007bff;
    color: white;
    display: inline-block;
}
.btn-small.btn-danger {
    background: #dc3545;
}
.btn-small:hover {
    opacity: 0.8;
}
</style>

<?php include '../includes/footer.php'; ?>