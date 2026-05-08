<?php
/**
 * My Watchlist - Complete Version with All Features
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check login
requireLogin();

$user_id = $_SESSION['user_id'];
$success_msg = isset($_GET['msg']) ? sanitizeText($_GET['msg']) : '';
$error_msg = isset($_GET['error']) ? sanitizeText($_GET['error']) : '';

// Get filters
$status_filter = isset($_GET['status']) ? validateStatus($_GET['status']) : null;
$priority_filter = isset($_GET['priority']) ? validatePriority($_GET['priority']) : null;

// Build query
$conditions = ["w.UserID = :uid"];
$params = [':uid' => $user_id];

if ($status_filter) {
    $conditions[] = "w.Status = :status";
    $params[':status'] = $status_filter;
}

if ($priority_filter) {
    $conditions[] = "w.Priority = :priority";
    $params[':priority'] = $priority_filter;
}

$whereClause = implode(" AND ", $conditions);

try {
    // Get watchlist with full details
    $sql = "
        SELECT w.*, m.Title, m.Genre, m.ReleaseYear
        FROM tblwatchlist w
        JOIN tblmovie m ON w.MovieID = m.MovieID
        WHERE $whereClause
        ORDER BY 
            CASE w.Priority WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 END,
            w.AddedDate DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $watchlist = $stmt->fetchAll();
    
    // Get statistics
    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN Status = 'To Watch' THEN 1 ELSE 0 END) as to_watch,
            SUM(CASE WHEN Status = 'Watching Now' THEN 1 ELSE 0 END) as watching_now,
            SUM(CASE WHEN Status = 'Watched' THEN 1 ELSE 0 END) as watched
        FROM tblwatchlist WHERE UserID = :uid
    ");
    $statsStmt->execute([':uid' => $user_id]);
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    $error_msg = "Error: " . $e->getMessage();
    $watchlist = [];
    $stats = ['total' => 0, 'to_watch' => 0, 'watching_now' => 0, 'watched' => 0];
}

include '../includes/header.php';
?>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>📋 My Watchlist</h1>
        <a href="add.php" class="btn btn-primary">➕ Add Movie</a>
    </div>
    
    <!-- Messages -->
    <?php if ($success_msg): ?>
        <div class="alert alert-success">✅ <?php echo $success_msg; ?></div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div class="alert alert-error">❌ <?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Movies</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['to_watch']; ?></div>
            <div class="stat-label">📋 To Watch</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['watching_now']; ?></div>
            <div class="stat-label">▶️ Watching Now</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['watched']; ?></div>
            <div class="stat-label">✅ Watched</div>
        </div>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="">
            <select name="status">
                <option value="">All Status</option>
                <option value="To Watch" <?php echo $status_filter == 'To Watch' ? 'selected' : ''; ?>>To Watch</option>
                <option value="Watching Now" <?php echo $status_filter == 'Watching Now' ? 'selected' : ''; ?>>Watching Now</option>
                <option value="Watched" <?php echo $status_filter == 'Watched' ? 'selected' : ''; ?>>Watched</option>
            </select>
            
            <select name="priority">
                <option value="">All Priority</option>
                <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>High Priority</option>
                <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Medium Priority</option>
                <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Low Priority</option>
            </select>
            
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="my_watchlist.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <!-- Watchlist Table -->
    <?php if (empty($watchlist)): ?>
        <div class="empty-message">
            <p>📭 Your watchlist is empty.</p>
            <a href="add.php" class="btn btn-primary">➕ Add Your First Movie</a>
        </div>
    <?php else: ?>
        <table class="watchlist-table">
            <thead>
                <tr>
                    <th>Movie Title</th>
                    <th>Genre</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Rating</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($watchlist as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($item['Title']); ?></strong>
                        <?php if (!empty($item['PersonalNotes'])): ?>
                            <br><small><?php echo htmlspecialchars(substr($item['PersonalNotes'], 0, 40)); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['Genre']); ?></td>
                    <td><?php echo $item['ReleaseYear']; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $item['Status'])); ?>">
                            <?php echo $item['Status']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="priority-badge priority-<?php echo strtolower($item['Priority']); ?>">
                            <?php echo $item['Priority']; ?>
                        </span>
                    </td>
                    <td><?php echo $item['PersonalRating'] ? $item['PersonalRating'] . '/10' : '-'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($item['AddedDate'])); ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?php echo $item['WatchlistID']; ?>" class="edit">✏️ Edit</a>
                        <a href="update_status.php?id=<?php echo $item['WatchlistID']; ?>" class="status-btn">📝 Status</a>
                        <a href="remove.php?id=<?php echo $item['WatchlistID']; ?>" class="delete" onclick="return confirm('Remove this movie?')">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>