<?php
require_once '../includes/config.php';
include '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE MovieID = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie):
?>
    <div class="container">
        <p>Movie not found.</p>
        <a href="index.php">Back to Movies</a>
    </div>
<?php else: ?>
    <div class="container">
        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 0 0 300px;">
                <?php if (!empty($movie['PosterURL'])): ?>
                    <img src="<?php echo $movie['PosterURL']; ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>" style="width: 100%; border-radius: 12px;">
                <?php else: ?>
                    <div style="width: 100%; height: 450px; background: #242c34; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                        <span style="font-size: 4rem;">🎬</span>
                    </div>
                <?php endif; ?>
            </div>
            <div style="flex: 1;">
                <h1><?php echo htmlspecialchars($movie['Title']); ?> (<?php echo $movie['ReleaseYear']; ?>)</h1>
                <p><strong>Genre:</strong> <?php echo $movie['Genre']; ?></p>
                <p><strong>Rating:</strong> ⭐ <?php echo $movie['TMDBRating']; ?>/10</p>
                <p><strong>Description:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($movie['Description'])); ?></p>
                
                <?php if (isLoggedIn()): ?>
                    <button class="btn-hero" style="margin-top: 20px;" onclick="location.href='../watchlist/add.php?id=<?php echo $movie['MovieID']; ?>'">
                        + Add to Watchlist
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>