<?php
require_once '../includes/config.php';
require_once '../includes/validation.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get all movies
$movies = $conn->query("SELECT MovieID, Title, ReleaseYear FROM tblmovie ORDER BY Title")->fetchAll();

// Get user's existing watchlist movies
$existing = $conn->prepare("SELECT MovieID FROM tblwatchlist WHERE UserID = :uid");
$existing->execute([':uid' => $user_id]);
$existingMovies = $existing->fetchAll(PDO::FETCH_COLUMN);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = validateMovieId($_POST['movie_id'] ?? '');
    $status = validateStatus($_POST['status'] ?? 'To Watch');
    $priority = validatePriority($_POST['priority'] ?? 'Medium');
    $rating = !empty($_POST['rating']) ? validateRating($_POST['rating']) : null;
    $notes = sanitizeText($_POST['notes'] ?? '');
    
    if (!$movie_id) {
        $error = "Please select a movie";
    } elseif (in_array($movie_id, $existingMovies)) {
        $error = "This movie is already in your watchlist!";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO tblwatchlist (UserID, MovieID, Status, Priority, PersonalRating, PersonalNotes, AddedDate)
            VALUES (:uid, :mid, :status, :priority, :rating, :notes, CURDATE())
        ");
        
        if ($stmt->execute([
            ':uid' => $user_id,
            ':mid' => $movie_id,
            ':status' => $status,
            ':priority' => $priority,
            ':rating' => $rating,
            ':notes' => $notes
        ])) {
            $success = "Movie added to your watchlist!";
            header("refresh:2;url=my_watchlist.php");
        } else {
            $error = "Failed to add movie";
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>➕ Add Movie to Watchlist</h1>
        <a href="my_watchlist.php" class="btn btn-secondary">← Back to Watchlist</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">✅ <?php echo $success; ?> Redirecting...</div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="form">
        <div class="form-group">
            <label>Select Movie *</label>
            <select name="movie_id" required>
                <option value="">-- Choose a movie --</option>
                <?php foreach ($movies as $movie): ?>
                    <option value="<?php echo $movie['MovieID']; ?>" <?php echo in_array($movie['MovieID'], $existingMovies) ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($movie['Title']); ?> (<?php echo $movie['ReleaseYear']; ?>)
                        <?php echo in_array($movie['MovieID'], $existingMovies) ? ' [Already in watchlist]' : ''; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="To Watch">📋 To Watch</option>
                <option value="Watching Now">▶️ Watching Now</option>
                <option value="Watched">✅ Watched</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Priority</label>
            <select name="priority">
                <option value="High">🔴 High</option>
                <option value="Medium" selected>🟡 Medium</option>
                <option value="Low">🟢 Low</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Your Rating (1-10)</label>
            <input type="number" name="rating" min="1" max="10" step="1">
        </div>
        
        <div class="form-group">
            <label>Personal Notes</label>
            <textarea name="notes" rows="3" placeholder="Add your thoughts about this movie..."></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">➕ Add to Watchlist</button>
            <a href="my_watchlist.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>