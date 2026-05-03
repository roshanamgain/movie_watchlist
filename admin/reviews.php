<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../admin_login.php');
    exit();
}
require_once '../includes/config.php';

$stmt = $pdo->query("
    SELECT r.*, u.FullName, m.Title 
    FROM tblreview r 
    JOIN tbluser u ON r.UserID = u.UserID 
    JOIN tblmovie m ON r.MovieID = m.MovieID 
    ORDER BY r.ReviewDate DESC
");
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Reviews</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #14181c; color: white; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 20px; color: #c41e3a; }
        table { width: 100%; border-collapse: collapse; background: #1c2228; border-radius: 12px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2c3440; }
        th { background: #14181c; color: #c41e3a; }
        .back { display: inline-block; margin-bottom: 20px; color: #c41e3a; text-decoration: none; padding: 8px 16px; background: #1c2228; border-radius: 8px; }
        .back:hover { background: #c41e3a; color: white; }
        .rating { color: #f5c518; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <h1>All Reviews</h1>
        <table>
            <thead>
                <tr><th>User</th><th>Movie</th><th>Rating</th><th>Review</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $review): ?>
                <tr>
                    <td><?php echo htmlspecialchars($review['FullName']); ?></td>
                    <td><?php echo htmlspecialchars($review['Title']); ?></td>
                    <td class="rating">⭐ <?php echo $review['Rating']; ?>/10</td>
                    <td><?php echo htmlspecialchars(substr($review['ReviewText'], 0, 50)); ?>...</td>
                    <td><?php echo date('M d, Y', strtotime($review['ReviewDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>