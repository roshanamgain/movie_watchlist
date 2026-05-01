<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Fetch all movies
$stmt = $pdo->query("SELECT * FROM movies ORDER BY release_year DESC");
$movies = $stmt->fetchAll();

// Fetch user's watchlist movie IDs
$watchlistStmt = $pdo->prepare("SELECT movie_id FROM watchlist WHERE user_id = ?");
$watchlistStmt->execute([$_SESSION['user_id']]);
$watchlistIds = $watchlistStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <h1>🎬 Movie Library</h1>
    <div class="movie-grid">
        <?php foreach ($movies as $movie): ?>
            <div class="movie-card">
                <?php if (!empty($movie['poster_url'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                <?php else: ?>
                    <div class="no-poster">No Poster</div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($movie['title']) ?> (<?= $movie['release_year'] ?>)</h3>
                <p class="genre"><?= htmlspecialchars($movie['genre']) ?></p>
                <p class="description"><?= htmlspecialchars(substr($movie['description'], 0, 100)) ?>...</p>
                
                <?php if (in_array($movie['id'], $watchlistIds)): ?>
                    <button class="btn added" disabled>✔ Already in Watchlist</button>
                <?php else: ?>
                    <a href="add_to_watchlist.php?movie_id=<?= $movie['id'] ?>" class="btn add">+ Add to Watchlist</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.movie-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 20px;
}
.movie-card {
    width: 200px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
    background: #f9f9f9;
}
.movie-card img {
    width: 100%;
    height: 280px;
    object-fit: cover;
    border-radius: 4px;
}
.no-poster {
    width: 100%;
    height: 280px;
    background: #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn {
    display: inline-block;
    padding: 6px 12px;
    margin-top: 10px;
    text-decoration: none;
    background: #007bff;
    color: white;
    border-radius: 4px;
}
.btn.added {
    background: #6c757d;
    cursor: default;
}
</style>

<?php include 'includes/footer.php'; ?>