<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../admin_login.php');
    exit();
}
require_once '../includes/config.php';

$stmt = $pdo->query("
    SELECT w.*, u.FullName, m.Title 
    FROM tblwatchlist w 
    JOIN tbluser u ON w.UserID = u.UserID 
    JOIN tblmovie m ON w.MovieID = m.MovieID 
    ORDER BY w.AddedDate DESC
");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Watchlist Items</title>
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
        .status { padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; }
        .status-to-watch { background: #f5c518; color: #14181c; }
        .status-watched { background: #00e054; color: #14181c; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <h1>All Watchlist Items</h1>
        <table>
            <thead>
                <tr><th>User</th><th>Movie</th><th>Status</th><th>Added Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['FullName']); ?></td>
                    <td><?php echo htmlspecialchars($item['Title']); ?></td>
                    <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $item['Status'])); ?>"><?php echo $item['Status']; ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($item['AddedDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>