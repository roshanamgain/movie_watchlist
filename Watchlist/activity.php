<?php
/**
 * User Activity Feed
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file displays a timeline of all user activities including:
 * - Adding movies to watchlist
 * - Removing movies from watchlist
 * - Updating status
 * - Changing ratings
 * - Writing reviews
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];

// Pagination setup
$page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
if (!$page || $page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get activity filter type
$filter_type = sanitizeText($_GET['filter'] ?? '');

try {
    // Check if activity table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'tbluser_activity'");
    $activityTableExists = $tableCheck->rowCount() > 0;
    
    if ($activityTableExists) {
        // Build query with filter
        $conditions = ["UserID = :uid"];
        $params = [':uid' => $user_id];
        
        if (!empty($filter_type)) {
            $conditions[] = "ActivityType = :type";
            $params[':type'] = $filter_type;
        }
        
        $whereClause = implode(" AND ", $conditions);
        
        // Get total count for pagination
        $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tbluser_activity WHERE $whereClause");
        $countStmt->execute($params);
        $totalActivities = $countStmt->fetch()['total'];
        $totalPages = ceil($totalActivities / $limit);
        
        // Get activities with movie info
        $stmt = $conn->prepare("
            SELECT a.*, m.Title as MovieTitle
            FROM tbluser_activity a
            LEFT JOIN tblmovie m ON a.MovieID = m.MovieID
            WHERE $whereClause
            ORDER BY a.Timestamp DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            if ($key != ':limit' && $key != ':offset') {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $activities = $stmt->fetchAll();
    } else {
        // If activity table doesn't exist, show message
        $activities = [];
        $totalActivities = 0;
        $totalPages = 0;
        $noTableMessage = "Activity tracking is being set up. Your actions will appear here soon!";
    }
    
    // Get activity statistics
    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(CASE WHEN ActivityType = 'ADDED_TO_WATCHLIST' THEN 1 END) as total_adds,
            COUNT(CASE WHEN ActivityType = 'STATUS_CHANGED' THEN 1 END) as total_status_changes,
            COUNT(CASE WHEN ActivityType = 'UPDATED_WATCHLIST_ENTRY' THEN 1 END) as total_updates
        FROM tbluser_activity
        WHERE UserID = :uid
    ");
    $statsStmt->execute([':uid' => $user_id]);
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error loading activities: " . $e->getMessage();
    $activities = [];
    $totalActivities = 0;
    $totalPages = 0;
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>📊 My Activity Feed</h1>
        <a href="../dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>
    
    <!-- Activity Summary Stats -->
    <?php if ($activityTableExists && isset($stats)): ?>
    <div class="stats-summary">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_adds'] ?? 0; ?></div>
            <div class="stat-label">Movies Added</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_status_changes'] ?? 0; ?></div>
            <div class="stat-label">Status Changes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_updates'] ?? 0; ?></div>
            <div class="stat-label">Updates Made</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $totalActivities; ?></div>
            <div class="stat-label">Total Activities</div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <label>Filter by activity:</label>
        <select onchange="window.location.href='activity.php?filter='+this.value">
            <option value="">All Activities</option>
            <option value="ADDED_TO_WATCHLIST" <?php echo $filter_type == 'ADDED_TO_WATCHLIST' ? 'selected' : ''; ?>>➕ Added to Watchlist</option>
            <option value="REMOVED_FROM_WATCHLIST" <?php echo $filter_type == 'REMOVED_FROM_WATCHLIST' ? 'selected' : ''; ?>>🗑️ Removed from Watchlist</option>
            <option value="STATUS_CHANGED" <?php echo $filter_type == 'STATUS_CHANGED' ? 'selected' : ''; ?>>📝 Status Changes</option>
            <option value="UPDATED_WATCHLIST_ENTRY" <?php echo $filter_type == 'UPDATED_WATCHLIST_ENTRY' ? 'selected' : ''; ?>>✏️ Entry Updates</option>
        </select>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($noTableMessage)): ?>
        <div class="alert alert-info">ℹ️ <?php echo $noTableMessage; ?></div>
    <?php endif; ?>
    
    <!-- Activity Timeline -->
    <div class="activity-timeline">
        <h2>Your Activity Timeline</h2>
        
        <?php if (empty($activities)): ?>
            <div class="alert alert-info">
                📭 No activities found. Start adding movies to your watchlist to see your activity here!
                <br><br>
                <a href="add.php" class="btn btn-primary">➕ Add Your First Movie</a>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($activities as $activity): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <?php
                            $icon = '📋';
                            switch($activity['ActivityType']) {
                                case 'ADDED_TO_WATCHLIST': $icon = '➕'; break;
                                case 'REMOVED_FROM_WATCHLIST': $icon = '🗑️'; break;
                                case 'STATUS_CHANGED': $icon = '📝'; break;
                                case 'UPDATED_WATCHLIST_ENTRY': $icon = '✏️'; break;
                                default: $icon = '📌';
                            }
                            echo $icon;
                            ?>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="activity-type"><?php echo str_replace('_', ' ', $activity['ActivityType']); ?></span>
                                <span class="activity-time"><?php echo date('F j, Y g:i A', strtotime($activity['Timestamp'])); ?></span>
                            </div>
                            <div class="activity-details">
                                <?php if ($activity['MovieTitle']): ?>
                                    <strong>Movie:</strong> <?php echo htmlspecialchars($activity['MovieTitle']); ?><br>
                                <?php endif; ?>
                                <?php if ($activity['Details']): ?>
                                    <strong>Details:</strong> <?php echo htmlspecialchars($activity['Details']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&filter=<?php echo urlencode($filter_type); ?>" class="page-link">← Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-current">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>&filter=<?php echo urlencode($filter_type); ?>" class="page-link">Next →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}
.stat-number {
    font-size: 28px;
    font-weight: bold;
}
.stat-label {
    font-size: 14px;
    margin-top: 5px;
    opacity: 0.9;
}
.filter-bar {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
}
.filter-bar select {
    padding: 8px 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}
.activity-timeline {
    background: white;
    border-radius: 10px;
    padding: 20px;
}
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item {
    position: relative;
    margin-bottom: 25px;
    display: flex;
}
.timeline-icon {
    position: absolute;
    left: -30px;
    width: 40px;
    height: 40px;
    background: #007bff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}
.timeline-content {
    flex: 1;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-left: 20px;
}
.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e0e0e0;
}
.activity-type {
    font-weight: bold;
    color: #007bff;
}
.activity-time {
    font-size: 12px;
    color: #666;
}
.activity-details {
    font-size: 14px;
    color: #555;
    line-height: 1.5;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
    padding: 15px;
}
.page-link {
    padding: 8px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.page-current {
    padding: 8px 15px;
    background: #e9ecef;
    border-radius: 5px;
}
</style>

<?php include '../includes/footer.php'; ?>