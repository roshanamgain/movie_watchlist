<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../admin_login.php');
    exit();
}
require_once '../includes/config.php';

// Get REGULAR users only (exclude admins)
$stmt = $pdo->query("SELECT UserID, FullName, Email, IsActive, CreatedDate, LastLoginDate FROM tbluser WHERE Role IS NULL OR Role = '' ORDER BY CreatedDate DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users - MovieWatchlist</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #14181c; color: white; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { margin-bottom: 20px; color: #c41e3a; }
        table { width: 100%; border-collapse: collapse; background: #1c2228; border-radius: 12px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #2c3440; }
        th { background: #14181c; color: #c41e3a; font-weight: 600; }
        tr:hover { background: #242c34; }
        .back { display: inline-block; margin-bottom: 20px; color: #c41e3a; text-decoration: none; padding: 8px 16px; background: #1c2228; border-radius: 8px; }
        .back:hover { background: #c41e3a; color: white; }
        .status-active { color: #00e054; font-weight: 600; }
        .status-inactive { color: #c41e3a; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <h1>All Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['UserID']; ?></td>
                    <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td><?php echo $user['LastLoginDate'] ? date('M d, Y', strtotime($user['LastLoginDate'])) : 'Never'; ?></td>
                    <td class="<?php echo $user['IsActive'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $user['IsActive'] ? 'Active' : 'Frozen'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['CreatedDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>