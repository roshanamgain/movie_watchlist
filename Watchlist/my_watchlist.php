<?php
/**
 * My Watchlist - Display User's Watchlist
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file displays the user's watchlist with:
 * - List all movies in watchlist (READ operation)
 * - Filter by status and priority
 * - Edit, Update Status, and Delete actions
 * - Statistics cards showing totals
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];
$success_msg = isset($_GET['msg']) ? sanitizeText($_GET['msg']) : '';
$error_msg = isset($_GET['error']) ? sanitizeText($_GET['error']) : '';

// Get filter parameters
$status_filter = isset($_GET['status']) ? validateStatus($_GET['status']) : null;
$priority_filter = isset($_GET['priority']) ? validatePriority($_GET['priority']) : null;

// Build query conditions
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
    // Get user's watchlist with movie details
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
    $watchlist = $stmt->fetchAll();
    
    // Get statistics for dashboard
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
    $error_msg = "Error loading watchlist: " . $e->getMessage();
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
    
    <!-- Success/Error Messages -->
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
                <option value="To Watch" <?php echo $status_filter == 'To Watch' ? 'selected' : ''; ?>>📋 To Watch</option>
                <option value="Watching Now" <?php echo $status_filter == 'Watching Now' ? 'selected' : ''; ?>>▶️ Watching Now</option>
                <option value="Watched" <?php echo $status_filter == 'Watched' ? 'selected' : ''; ?>>✅ Watched</option>
            </select>
            
            <select name="priority">
                <option value="">All Priority</option>
                <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>🔴 High</option>
                <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>🟢 Low</option>
            </select>
            
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="my_watchlist.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <!-- Watchlist Table -->
    <?php if (empty($watchlist)): ?>
        <div class="empty-message">
            <p>📭 Your watchlist is empty.</p>
            <p><a href="add.php" class="btn btn-primary">➕ Add Your First Movie</a></p>
            <p>or <a href="find.php">🔍 Search for movies</a> to add</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table class="watchlist-table">
                <thead>
                    <tr>
                        <th>Movie Title</th>
                        <th>Genre</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Your Rating</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watchlist as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['Title']); ?></strong>
                                <?php if (!empty($item['PersonalNotes'])): ?>
                                    <br>
                                    <small style="color:#6c757d;">📝 <?php echo htmlspecialchars(substr($item['PersonalNotes'], 0, 50)); ?></small>
                                <?php endif; ?>
                             </td>
                            <td><?php echo htmlspecialchars($item['Genre']); ?> </td>
                            <td><?php echo $item['ReleaseYear']; ?> </td>
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
                            <td>
                                <?php if ($item['PersonalRating']): ?>
                                    <strong><?php echo $item['PersonalRating']; ?>/10</strong>
                                    <?php if ($item['PersonalRating'] >= 8): ?>⭐
                                    <?php elseif ($item['PersonalRating'] >= 5): ?>👍
                                    <?php else: ?>👎
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#6c757d;">Not rated</span>
                                <?php endif; ?>
                             </td>
                            <td><?php echo date('M d, Y', strtotime($item['AddedDate'])); ?> </td>
                            <td class="actions">
                                <a href="edit.php?id=<?php echo $item['WatchlistID']; ?>" class="edit" title="Edit">✏️ Edit</a>
                                <a href="update_status.php?id=<?php echo $item['WatchlistID']; ?>" class="status-btn" title="Update Status">📝 Status</a>
                                <a href="remove.php?id=<?php echo $item['WatchlistID']; ?>" class="delete" title="Remove" 
                                   onclick="return confirm('Remove &quot;<?php echo htmlspecialchars($item['Title']); ?>&quot; from your watchlist?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-20" style="color:#6c757d; font-size:12px;">
            Total: <?php echo count($watchlist); ?> movies in your watchlist
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>