<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../admin_login.php');
    exit();
}
require_once '../includes/config.php';

// Get ONLY admin users (Role is NOT NULL)
$stmt = $pdo->query("SELECT UserID, FullName, Email, Role, IsActive, CreatedDate, LastLoginDate FROM tbluser WHERE Role IS NOT NULL AND Role != '' ORDER BY CreatedDate DESC");
$admins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Users - MovieWatchlist</title>
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
        .role-badge { background: #c41e3a; padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; display: inline-block; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <h1>Admin Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo $admin['UserID']; ?></td>
                    <td><?php echo htmlspecialchars($admin['FullName']); ?></td>
                    <td><?php echo htmlspecialchars($admin['Email']); ?></td>
                    <td><span class="role-badge"><?php echo htmlspecialchars($admin['Role']); ?></span></td>
                    <td><?php echo $admin['LastLoginDate'] ? date('M d, Y', strtotime($admin['LastLoginDate'])) : 'Never'; ?></td>
                    <td class="<?php echo $admin['IsActive'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $admin['IsActive'] ? 'Active' : 'Frozen'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($admin['CreatedDate'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($admins) == 0): ?>
            <p style="text-align: center; margin-top: 20px;">No admin users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>