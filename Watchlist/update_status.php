<?php
/**
 * Quick Update Watch Status Only
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file provides a quick way to update just the status of a movie
 * without editing all other fields.
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];
$watchlist_id = isset($_GET['id']) ? validateWatchlistId($_GET['id']) : null;

// Redirect if no valid ID
if (!$watchlist_id) {
    header("Location: my_watchlist.php?error=Invalid+request");
    exit();
}

// Process form submission (UPDATE status only)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = validateStatus($_POST['status'] ?? '');
    
    if ($new_status) {
        try {
            // Get movie title for logging
            $infoStmt = $conn->prepare("
                SELECT m.Title FROM tblwatchlist w
                JOIN tblmovie m ON w.MovieID = m.MovieID
                WHERE w.WatchlistID = :id AND w.UserID = :uid
            ");
            $infoStmt->execute([':id' => $watchlist_id, ':uid' => $user_id]);
            $movie = $infoStmt->fetch();
            
            // Update only the status
            $stmt = $conn->prepare("
                UPDATE tblwatchlist 
                SET Status = :status 
                WHERE WatchlistID = :id AND UserID = :uid
            ");
            $stmt->execute([
                ':status' => $new_status, 
                ':id' => $watchlist_id, 
                ':uid' => $user_id
            ]);
            
            // Log activity
            logActivity($conn, $user_id, 'STATUS_CHANGED', null, 
                "Changed status for '{$movie['Title']}' from to: $new_status");
            
            // Redirect back to watchlist with success message
            header("Location: my_watchlist.php?msg=Status+updated+successfully");
            exit();
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Invalid status selected.";
    }
}

// Get current entry to display current status
try {
    $stmt = $conn->prepare("
        SELECT w.*, m.Title, m.Genre
        FROM tblwatchlist w
        JOIN tblmovie m ON w.MovieID = m.MovieID
        WHERE w.WatchlistID = :id AND w.UserID = :uid
    ");
    $stmt->execute([':id' => $watchlist_id, ':uid' => $user_id]);
    $entry = $stmt->fetch();
    
    if (!$entry) {
        header("Location: my_watchlist.php?error=Item+not+found");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error loading entry: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>📝 Update Watch Status</h1>
        <a href="my_watchlist.php" class="btn btn-secondary">← Back to Watchlist</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="movie-info-box">
        <h3><?php echo htmlspecialchars($entry['Title']); ?></h3>
        <p><strong>Genre:</strong> <?php echo $entry['Genre']; ?></p>
        <div class="current-status">
            <p><strong>Current Status:</strong> 
                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $entry['Status'])); ?>">
                    <?php echo $entry['Status']; ?>
                </span>
            </p>
        </div>
    </div>
    
    <form method="POST" action="" class="form">
        <div class="form-group">
            <label for="status">Change Status To:</label>
            <select name="status" id="status">
                <option value="To Watch" <?php echo $entry['Status'] == 'To Watch' ? 'selected' : ''; ?>>📋 To Watch - Plan to watch later</option>
                <option value="Watching Now" <?php echo $entry['Status'] == 'Watching Now' ? 'selected' : ''; ?>>▶️ Watching Now - Currently watching</option>
                <option value="Watched" <?php echo $entry['Status'] == 'Watched' ? 'selected' : ''; ?>>✅ Watched - Finished watching</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Status</button>
            <a href="my_watchlist.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
.movie-info-box {
    background: #f0f8ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}
.current-status {
    background: #fff;
    padding: 10px;
    border-radius: 5px;
    margin-top: 10px;
}
.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
}
.status-to-watch { background: #ffc107; color: #333; }
.status-watching-now { background: #17a2b8; color: white; }
.status-watched { background: #28a745; color: white; }
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
</style>

<?php include '../includes/footer.php'; ?>