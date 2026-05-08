<?php
/**
 * Edit Watchlist Entry
 * Author: Bishnu - Watchlist Component
 * Date: May 2026
 * 
 * This file allows users to edit existing watchlist entries including:
 * - Status (To Watch, Watching Now, Watched)
 * - Priority (High, Medium, Low)
 * - Rating (1-10)
 * - Personal Notes
 * - Watching Progress
 */

require_once '../includes/config.php';
require_once '../includes/validation.php';

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];
$watchlist_id = isset($_GET['id']) ? validateWatchlistId($_GET['id']) : null;
$success = '';
$error = '';

// Redirect if no valid ID
if (!$watchlist_id) {
    header("Location: my_watchlist.php?error=Invalid+request");
    exit();
}

// Get current entry from database
try {
    $stmt = $conn->prepare("
        SELECT w.*, m.Title, m.Genre, m.ReleaseYear, m.TMDBRating
        FROM tblwatchlist w
        JOIN tblmovie m ON w.MovieID = m.MovieID
        WHERE w.WatchlistID = :id AND w.UserID = :uid
    ");
    $stmt->execute([':id' => $watchlist_id, ':uid' => $user_id]);
    $entry = $stmt->fetch();
    
    // If entry not found, redirect
    if (!$entry) {
        header("Location: my_watchlist.php?error=Item+not+found");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error loading entry: " . $e->getMessage();
}

// Process form submission (UPDATE operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate all inputs
    $status = validateStatus($_POST['status'] ?? '');
    $priority = validatePriority($_POST['priority'] ?? '');
    $rating = !empty($_POST['rating']) ? validateRating($_POST['rating']) : null;
    $notes = sanitizeText($_POST['notes'] ?? '');
    $progress = sanitizeText($_POST['watching_progress'] ?? '');
    
    // Check if validation passed
    if ($status && $priority) {
        try {
            // Update the watchlist entry
            $update = $conn->prepare("
                UPDATE tblwatchlist 
                SET Status = :status, 
                    Priority = :priority, 
                    PersonalRating = :rating, 
                    PersonalNotes = :notes,
                    WatchingProgress = :progress
                WHERE WatchlistID = :id AND UserID = :uid
            ");
            
            $result = $update->execute([
                ':status' => $status,
                ':priority' => $priority,
                ':rating' => $rating,
                ':notes' => $notes,
                ':progress' => $progress,
                ':id' => $watchlist_id,
                ':uid' => $user_id
            ]);
            
            if ($result) {
                // Log activity for tracking
                logActivity($conn, $user_id, 'UPDATED_WATCHLIST_ENTRY', $entry['MovieID'], 
                    "Updated watchlist entry for '{$entry['Title']}'");
                
                $success = "Watchlist entry updated successfully!";
                
                // Refresh entry data to show updated values
                $stmt->execute([':id' => $watchlist_id, ':uid' => $user_id]);
                $entry = $stmt->fetch();
            } else {
                $error = "Failed to update entry.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please select valid status and priority.";
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>✏️ Edit: <?php echo htmlspecialchars($entry['Title']); ?></h1>
        <a href="my_watchlist.php" class="btn btn-secondary">← Back to Watchlist</a>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Movie Information Display -->
    <div class="movie-info-box">
        <h3>Movie Information</h3>
        <p><strong>Title:</strong> <?php echo htmlspecialchars($entry['Title']); ?> (<?php echo $entry['ReleaseYear']; ?>)</p>
        <p><strong>Genre:</strong> <?php echo $entry['Genre']; ?></p>
        <p><strong>TMDB Rating:</strong> <?php echo $entry['TMDBRating'] ?? 'N/A'; ?>/10</p>
        <p><strong>Added to Watchlist:</strong> <?php echo $entry['AddedDate']; ?></p>
    </div>
    
    <!-- Edit Form -->
    <form method="POST" action="" class="form">
        <div class="form-group">
            <label for="status">Watch Status *</label>
            <select name="status" id="status">
                <option value="To Watch" <?php echo $entry['Status'] == 'To Watch' ? 'selected' : ''; ?>>📋 To Watch</option>
                <option value="Watching Now" <?php echo $entry['Status'] == 'Watching Now' ? 'selected' : ''; ?>>▶️ Watching Now</option>
                <option value="Watched" <?php echo $entry['Status'] == 'Watched' ? 'selected' : ''; ?>>✅ Watched</option>
            </select>
            <small>Update your current watching progress</small>
        </div>
        
        <div class="form-group">
            <label for="priority">Priority Level *</label>
            <select name="priority" id="priority">
                <option value="High" <?php echo $entry['Priority'] == 'High' ? 'selected' : ''; ?>>🔴 High - Watch soon!</option>
                <option value="Medium" <?php echo $entry['Priority'] == 'Medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                <option value="Low" <?php echo $entry['Priority'] == 'Low' ? 'selected' : ''; ?>>🟢 Low - Can wait</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="watching_progress">Watching Progress</label>
            <input type="text" name="watching_progress" id="watching_progress" 
                   value="<?php echo htmlspecialchars($entry['WatchingProgress'] ?? ''); ?>"
                   placeholder="e.g., 45 minutes in, halfway through, Season 2 Episode 3">
            <small>Track where you left off</small>
        </div>
        
        <div class="form-group">
            <label for="rating">Your Rating (1-10)</label>
            <input type="number" name="rating" id="rating" min="1" max="10" step="1"
                   value="<?php echo $entry['PersonalRating']; ?>">
            <small>Rate the movie after watching</small>
        </div>
        
        <div class="form-group">
            <label for="notes">Personal Notes</label>
            <textarea name="notes" id="notes" rows="4" 
                      placeholder="Write your thoughts about this movie..."><?php echo htmlspecialchars($entry['PersonalNotes'] ?? ''); ?></textarea>
            <small>Add your personal review or memories</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="remove.php?id=<?php echo $watchlist_id; ?>" class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to remove this movie from your watchlist?')">🗑️ Delete Entry</a>
        </div>
    </form>
</div>

<style>
.movie-info-box {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}
.movie-info-box h3 {
    margin-top: 0;
    color: #333;
}
.movie-info-box p {
    margin: 8px 0;
}
.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.btn-danger {
    background: #dc3545;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 5px;
}
.btn-danger:hover {
    background: #c82333;
}
</style>

<?php include '../includes/footer.php'; ?>