<?php
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ? AND Role IS NOT NULL AND IsActive = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['admin_id'] = $user['UserID'];
        $_SESSION['admin_name'] = $user['FullName'];
        $_SESSION['admin_role'] = $user['Role'];
        $_SESSION['is_admin'] = true;
        
        header('Location: admin/dashboard.php');
        exit();
    } else {
        $error = 'Invalid admin credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MovieWatchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #14181c; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .admin-login-container { max-width: 420px; width: 90%; background: #1c2228; border-radius: 16px; padding: 40px; border: 1px solid #2c3440; }
        .admin-logo { text-align: center; margin-bottom: 30px; }
        .admin-logo span { font-size: 3rem; }
        .admin-logo h1 { color: #c41e3a; font-size: 1.5rem; margin-top: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #99aabb; }
        .form-group input { width: 100%; padding: 12px; background: #2c3440; border: 1px solid #3a454d; border-radius: 8px; color: white; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #c41e3a; }
        .btn-admin-login { width: 100%; padding: 12px; background: #c41e3a; color: white; border: none; border-radius: 30px; font-weight: 600; cursor: pointer; font-size: 1rem; }
        .btn-admin-login:hover { background: #a01830; }
        .error-msg { background: rgba(196,30,58,0.2); border: 1px solid #c41e3a; color: #ff6666; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #99aabb; text-decoration: none; font-size: 0.85rem; }
        .back-link a:hover { color: #c41e3a; }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-logo">
            <span>🔐</span>
            <h1>Admin Login</h1>
        </div>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="email" placeholder="admin@moviewatchlist.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-admin-login">Login as Admin</button>
        </form>
        <div class="back-link">
            <a href="index.php">← Back to Main Site</a>
        </div>
    </div>
</body>
</html>