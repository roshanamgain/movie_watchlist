<?php
require_once '../includes/config.php';
include '../includes/header.php';

$searchTerm = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '';
$stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE Title LIKE ? ORDER BY Title");
$stmt->execute([$searchTerm]);
$results = $stmt->fetchAll();
?>

<div class="movie-section">
    <div class="section-header">
        <h2 class="section-title">SEARCH RESULTS FOR "<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"</h2>
        <a href="../movies/index.php" class="section-link">BACK TO FILMS →</a>
    </div>
    
    <?php if (count($results) > 0): ?>
        <div class="film-strip">
            <?php foreach ($results as $film): ?>
            <div class="movie-card" onclick="location.href='details.php?id=<?php echo $film['MovieID']; ?>'">
                <div class="movie-poster">
                    <?php if (!empty($film['PosterURL'])): ?>
                        <img src="<?php echo $film['PosterURL']; ?>" alt="<?php echo htmlspecialchars($film['Title']); ?>" class="movie-poster-img">
                    <?php else: ?>
                        <span class="movie-poster-placeholder">🎬</span>
                    <?php endif; ?>
                </div>
                <div class="movie-info">
                    <div class="movie-title"><?php echo htmlspecialchars($film['Title']); ?></div>
                    <div class="movie-year"><?php echo $film['ReleaseYear']; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No movies found matching your search.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>