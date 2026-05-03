<?php
require_once '../includes/config.php';
include '../includes/header.php';

$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM tblmovie WHERE MovieID = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    echo '<div class="container"><p>Movie not found.</p><a href="index.php">Back to Movies</a></div>';
    include '../includes/footer.php';
    exit();
}

// Display success/error messages
if (isset($_SESSION['review_success'])) {
    echo '<div class="alert-success" style="max-width: 1000px; margin: 100px auto 0 auto; padding: 12px 20px;">✅ ' . $_SESSION['review_success'] . '</div>';
    unset($_SESSION['review_success']);
}
if (isset($_SESSION['review_error'])) {
    echo '<div class="alert-error" style="max-width: 1000px; margin: 100px auto 0 auto; padding: 12px 20px;">❌ ' . $_SESSION['review_error'] . '</div>';
    unset($_SESSION['review_error']);
}
?>

<div class="movie-details-container">
    <div class="movie-details">
        <div class="movie-poster-large">
            <?php if (!empty($movie['PosterURL'])): ?>
                <img src="<?php echo $movie['PosterURL']; ?>" alt="<?php echo htmlspecialchars($movie['Title']); ?>">
            <?php else: ?>
                <span>🎬</span>
            <?php endif; ?>
        </div>
        <div class="movie-info-large">
            <h1><?php echo htmlspecialchars($movie['Title']); ?> (<?php echo $movie['ReleaseYear']; ?>)</h1>
            <p class="movie-genre">🎭 <?php echo htmlspecialchars($movie['Genre']); ?></p>
            <p class="movie-rating-large">⭐ <?php echo $movie['TMDBRating']; ?>/10</p>
            <p class="movie-description"><?php echo nl2br(htmlspecialchars($movie['Description'])); ?></p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="add-to-watchlist-btn-large" data-movie-id="<?php echo $movie['MovieID']; ?>">+ Add to My Watchlist</button>
            <?php endif; ?>
            
            <a href="index.php" class="back-btn">← Back to Movies</a>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- REVIEW SECTION - Add Review Form              -->
    <!-- ============================================ -->
    <div class="review-section">
        <h3>📝 Write a Review</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php
            // Check if user already reviewed this movie
            $stmt = $pdo->prepare("SELECT * FROM tblreview WHERE UserID = ? AND MovieID = ?");
            $stmt->execute([$_SESSION['user_id'], $movie['MovieID']]);
            $existingReview = $stmt->fetch();
            ?>
            
            <?php if ($existingReview): ?>
                <div class="existing-review">
                    <p>You already reviewed this movie:</p>
                    <div class="your-review">
                        <strong>Your rating:</strong> ⭐ <?php echo $existingReview['Rating']; ?>/10<br>
                        <strong>Your review:</strong> <?php echo nl2br(htmlspecialchars($existingReview['ReviewText'])); ?>
                    </div>
                    <button onclick="showEditReviewForm()" class="btn-edit-review">✏️ Edit Review</button>
                </div>
                
                <div id="editReviewForm" style="display: none;">
                    <form method="POST" action="add_review.php">
                        <input type="hidden" name="movie_id" value="<?php echo $movie['MovieID']; ?>">
                        <div class="rating-input">
                            <label>Your Rating (1-10):</label>
                            <select name="rating" required>
                                <option value="">Select rating</option>
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $existingReview['Rating'] == $i ? 'selected' : ''; ?>><?php echo $i; ?> stars</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="review-input">
                            <label>Your Review:</label>
                            <textarea name="review_text" rows="4" placeholder="Share your thoughts about this movie..."><?php echo htmlspecialchars($existingReview['ReviewText']); ?></textarea>
                        </div>
                        <button type="submit" name="update_review" class="btn-submit-review">Update Review</button>
                        <button type="button" onclick="hideEditReviewForm()" class="btn-cancel-review">Cancel</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="POST" action="add_review.php">
                    <input type="hidden" name="movie_id" value="<?php echo $movie['MovieID']; ?>">
                    <div class="rating-input">
                        <label>Your Rating (1-10):</label>
                        <select name="rating" required>
                            <option value="">Select rating</option>
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> stars</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="review-input">
                        <label>Your Review:</label>
                        <textarea name="review_text" rows="4" placeholder="Share your thoughts about this movie..." required></textarea>
                    </div>
                    <button type="submit" name="add_review" class="btn-submit-review">Submit Review</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p class="login-to-review">📝 <a href="../login.php">Login</a> to write a review for this movie.</p>
        <?php endif; ?>
    </div>

    <!-- ============================================ -->
    <!-- ALL REVIEWS SECTION                          -->
    <!-- ============================================ -->
    <div class="reviews-list">
        <h3>📖 User Reviews</h3>
        <?php
        $stmt = $pdo->prepare("
            SELECT r.*, u.FullName 
            FROM tblreview r 
            JOIN tbluser u ON r.UserID = u.UserID 
            WHERE r.MovieID = ? 
            ORDER BY r.ReviewDate DESC
        ");
        $stmt->execute([$movie['MovieID']]);
        $reviews = $stmt->fetchAll();
        ?>
        
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="user-review">
                    <div class="review-header">
                        <strong>🎭 <?php echo htmlspecialchars($review['FullName']); ?></strong>
                        <span class="review-rating">⭐ <?php echo $review['Rating']; ?>/10</span>
                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['ReviewDate'])); ?></span>
                    </div>
                    <div class="review-text">
                        <?php echo nl2br(htmlspecialchars($review['ReviewText'])); ?>
                    </div>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['UserID']): ?>
                        <div class="review-actions">
                            <a href="edit_review.php?id=<?php echo $review['ReviewID']; ?>&movie_id=<?php echo $movie['MovieID']; ?>" class="btn-edit">✏️ Edit</a>
                            <a href="delete_review.php?id=<?php echo $review['ReviewID']; ?>&movie_id=<?php echo $movie['MovieID']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete your review?')">🗑️ Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-reviews">No reviews yet. Be the first to review this movie!</p>
        <?php endif; ?>
    </div>
</div>

<style>
.movie-details-container {
    max-width: 1000px;
    margin: 100px auto 40px;
    padding: 0 20px;
}

.movie-details {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
    background: #1c2228;
    padding: 30px;
    border-radius: 16px;
}

.movie-poster-large {
    flex: 0 0 250px;
}

.movie-poster-large img {
    width: 100%;
    border-radius: 12px;
}

.movie-poster-large span {
    width: 100%;
    height: 375px;
    background: #2c3440;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    border-radius: 12px;
}

.movie-info-large {
    flex: 1;
}

.movie-info-large h1 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.movie-genre {
    color: #99aabb;
    margin-bottom: 10px;
}

.movie-rating-large {
    color: #f5c518;
    font-size: 1.2rem;
    margin-bottom: 20px;
}

.movie-description {
    line-height: 1.6;
    margin-bottom: 25px;
}

.add-to-watchlist-btn-large {
    background: #c41e3a;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    cursor: pointer;
    margin-right: 15px;
}

.back-btn {
    color: #99aabb;
    text-decoration: none;
}

/* Review Section Styles */
.review-section {
    background: #1c2228;
    border-radius: 16px;
    padding: 25px;
    margin-top: 30px;
}

.review-section h3 {
    color: white;
    margin-bottom: 20px;
}

.rating-input, .review-input {
    margin-bottom: 15px;
}

.rating-input label, .review-input label {
    display: block;
    margin-bottom: 8px;
    color: #99aabb;
}

.rating-input select, .review-input textarea {
    width: 100%;
    padding: 10px;
    background: #2c3440;
    border: 1px solid #3a454d;
    border-radius: 8px;
    color: white;
}

.btn-submit-review {
    background: #c41e3a;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
}

.btn-submit-review:hover {
    background: #a01830;
}

.btn-cancel-review {
    background: #2c3440;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
    margin-left: 10px;
}

.login-to-review {
    text-align: center;
    padding: 20px;
    color: #99aabb;
}

.login-to-review a {
    color: #c41e3a;
    text-decoration: none;
}

.reviews-list {
    background: #1c2228;
    border-radius: 16px;
    padding: 25px;
    margin-top: 20px;
}

.reviews-list h3 {
    color: white;
    margin-bottom: 20px;
}

.user-review {
    background: #242c34;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
}

.review-header {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.review-header strong {
    color: white;
}

.review-rating {
    color: #f5c518;
}

.review-date {
    color: #556677;
    font-size: 0.7rem;
}

.review-text {
    color: #99aabb;
    line-height: 1.5;
    margin-bottom: 10px;
}

.review-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-edit, .btn-delete {
    padding: 5px 12px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.7rem;
    font-weight: 600;
    display: inline-block;
}

.btn-edit {
    background: #00c2ff;
    color: #14181c;
}

.btn-delete {
    background: #c41e3a;
    color: white;
}

.existing-review {
    background: #242c34;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.your-review {
    margin: 10px 0;
    color: #99aabb;
}

.btn-edit-review {
    background: #00c2ff;
    color: #14181c;
    padding: 8px 16px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-weight: 600;
}

.no-reviews {
    text-align: center;
    color: #99aabb;
    padding: 20px;
}

.alert-success {
    background: rgba(0, 224, 84, 0.15);
    border: 1px solid #00e054;
    color: #00e054;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background: rgba(196, 30, 58, 0.15);
    border: 1px solid #c41e3a;
    color: #ff6666;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>

<script>
function showEditReviewForm() {
    document.getElementById('editReviewForm').style.display = 'block';
}

function hideEditReviewForm() {
    document.getElementById('editReviewForm').style.display = 'none';
}

// Add to watchlist functionality
document.querySelectorAll('.add-to-watchlist-btn-large').forEach(button => {
    if (button) {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const movieId = this.dataset.movieId;
            
            fetch('../watchlist/add_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'movie_id=' + movieId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.textContent = '✓ Added to Watchlist';
                    this.style.background = '#00e054';
                    this.style.color = '#14181c';
                    setTimeout(() => {
                        this.textContent = '+ Add to My Watchlist';
                        this.style.background = '#c41e3a';
                        this.style.color = 'white';
                    }, 2000);
                } else {
                    alert(data.error || 'Failed to add');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>