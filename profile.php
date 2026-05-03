<?php
require_once 'includes/config.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM tbluser WHERE UserID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as watchlist_count FROM tblwatchlist WHERE UserID = ?");
$stmt->execute([$userId]);
$watchlistCount = $stmt->fetch()['watchlist_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as watched_count FROM tblwatchlist WHERE UserID = ? AND Status = 'Watched'");
$stmt->execute([$userId]);
$watchedCount = $stmt->fetch()['watched_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as review_count FROM tblreview WHERE UserID = ?");
$stmt->execute([$userId]);
$reviewCount = $stmt->fetch()['review_count'];

// Get recent activity
$stmt = $pdo->prepare("
    SELECT w.*, m.Title, m.ReleaseYear, m.PosterURL 
    FROM tblwatchlist w 
    JOIN tblmovie m ON w.MovieID = m.MovieID 
    WHERE w.UserID = ? 
    ORDER BY w.AddedDate DESC LIMIT 4
");
$stmt->execute([$userId]);
$recentActivity = $stmt->fetchAll();
?>

<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <div class="avatar-inner">🎭</div>
        </div>
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['FullName']); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($user['Email']); ?></p>
            <div class="profile-badge">
                <span class="badge">Member since <?php echo date('M Y', strtotime($user['CreatedDate'])); ?></span>
            </div>
        </div>
        <div class="profile-actions-header">
            <a href="settings.php" class="action-btn edit-btn">
                <span>✏️</span> Edit Profile
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📽️</div>
            <div class="stat-number"><?php echo $watchlistCount; ?></div>
            <div class="stat-label">In Watchlist</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?php echo $watchedCount; ?></div>
            <div class="stat-label">Films Watched</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?php echo $reviewCount; ?></div>
            <div class="stat-label">Reviews</div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="recent-activity">
        <div class="section-header">
            <h2>Recent Activity</h2>
            <a href="watchlist/index.php" class="view-all">View All →</a>
        </div>
        
        <?php if (count($recentActivity) > 0): ?>
            <div class="activity-grid">
                <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-card" onclick="location.href='movies/details.php?id=<?php echo $activity['MovieID']; ?>'">
                    <div class="activity-poster">
                        <?php if (!empty($activity['PosterURL'])): ?>
                            <img src="<?php echo $activity['PosterURL']; ?>" alt="<?php echo htmlspecialchars($activity['Title']); ?>">
                        <?php else: ?>
                            <span>🎬</span>
                        <?php endif; ?>
                    </div>
                    <div class="activity-info">
                        <h4><?php echo htmlspecialchars($activity['Title']); ?></h4>
                        <p><?php echo $activity['ReleaseYear']; ?></p>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $activity['Status'])); ?>">
                            <?php echo $activity['Status']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No recent activity. Start adding movies to your watchlist!</p>
                <a href="movies/index.php" class="browse-btn">Browse Movies</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Profile Page Styles */
.profile-container {
    max-width: 1000px;
    margin: 100px auto 60px;
    padding: 0 24px;
}

/* Profile Header */
.profile-header {
    background: linear-gradient(135deg, #1c2228 0%, #14181c 100%);
    border-radius: 20px;
    padding: 32px;
    display: flex;
    gap: 28px;
    flex-wrap: wrap;
    margin-bottom: 32px;
    border: 1px solid #2c3440;
}

.profile-avatar {
    flex-shrink: 0;
}

.avatar-inner {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #c41e3a, #a01830);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.profile-info {
    flex: 1;
}

.profile-info h1 {
    font-size: 1.8rem;
    margin-bottom: 4px;
    color: #ffffff;
}

.profile-username {
    color: #00e054;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.profile-email {
    color: #99aabb;
    font-size: 0.85rem;
    margin-bottom: 12px;
}

.profile-badge {
    display: flex;
    gap: 10px;
}

.badge {
    background: rgba(0, 224, 84, 0.15);
    border: 1px solid #00e054;
    color: #00e054;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
}

.profile-actions-header {
    display: flex;
    align-items: center;
}

.action-btn {
    padding: 10px 24px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.edit-btn {
    background: #c41e3a;
    color: white;
}

.edit-btn:hover {
    background: #a01830;
    transform: translateY(-2px);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    background: #1c2228;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    transition: transform 0.2s;
    border: 1px solid #2c3440;
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: #c41e3a;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 12px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #00e054;
    font-family: 'Libre Baskerville', serif;
}

.stat-label {
    color: #99aabb;
    font-size: 0.85rem;
    margin-top: 8px;
}

/* Recent Activity */
.recent-activity {
    background: #1c2228;
    border-radius: 20px;
    padding: 28px;
    border: 1px solid #2c3440;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #2c3440;
}

.section-header h2 {
    font-size: 1.3rem;
    font-weight: 600;
}

.view-all {
    color: #99aabb;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s;
}

.view-all:hover {
    color: #c41e3a;
}

.activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}

.activity-card {
    background: #242c34;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
}

.activity-card:hover {
    transform: translateY(-4px);
}

.activity-poster {
    width: 100%;
    height: 200px;
    background: #2c3440;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.activity-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.activity-info {
    padding: 12px;
}

.activity-info h4 {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-info p {
    font-size: 0.7rem;
    color: #556677;
    margin-bottom: 8px;
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.65rem;
    font-weight: 600;
}

.status-to-watch {
    background: #f5c518;
    color: #14181c;
}

.status-watching {
    background: #00c2ff;
    color: #14181c;
}

.status-watched {
    background: #00e054;
    color: #14181c;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #99aabb;
}

.browse-btn {
    display: inline-block;
    margin-top: 16px;
    padding: 10px 24px;
    background: #c41e3a;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .profile-container {
        margin-top: 80px;
        padding: 0 16px;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-actions-header {
        justify-content: center;
    }
    
    .stats-grid {
        gap: 12px;
    }
    
    .stat-card {
        padding: 16px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .activity-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
    
    .activity-poster {
        height: 160px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>