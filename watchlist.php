<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

// Fetch user's watchlist with movie details
$stmt = $pdo->prepare("
    SELECT w.id as watchlist_id, w.watched, m.* 
    FROM watchlist w
    JOIN movies m ON w.movie_id = m.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$watchlist = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<div class="container">
    <h1>📋 My Watchlist</h1>
    <?php if (empty($watchlist)): ?>
        <p>Your watchlist is empty. <a href="movies.php">Browse movies</a> to add some!</p>
    <?php else: ?>
        <table class="watchlist-table">
            <thead>
                <tr><th>Poster</th><th>Title</th><th>Year</th><th>Genre</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($watchlist as $item): ?>
                <tr>
                    <td>
                        <?php if ($item['poster_url']): ?>
                            <img src="<?= htmlspecialchars($item['poster_url']) ?>" width="50">
                        <?php else: ?>🎬<?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['title']) ?></td>
                    <td><?= $item['release_year'] ?></td>
                    <td><?= htmlspecialchars($item['genre']) ?></td>
                    <td>
                        <?php if ($item['watched']): ?>
                            ✅ Watched
                        <?php else: ?>
                            ⏳ Pending
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="toggle_watched.php?watchlist_id=<?= $item['watchlist_id'] ?>" class="btn-small">Toggle Watched</a>
                        <a href="remove_from_watchlist.php?watchlist_id=<?= $item['watchlist_id'] ?>" class="btn-small delete" onclick="return confirm('Remove from watchlist?')">Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.watchlist-table {
    width: 100%;
    border-collapse: collapse;
}
.watchlist-table th, .watchlist-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.btn-small {
    display: inline-block;
    padding: 4px 8px;
    margin-right: 5px;
    background: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    font-size: 12px;
}
.delete {
    background: #dc3545;
}
</style>

<?php include 'includes/footer.php'; ?>