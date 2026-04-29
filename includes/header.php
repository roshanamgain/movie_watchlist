<!DOCTYPE html>
<html>
<head>
    <title>Movie Watchlist</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .navbar { background: #333; padding: 15px; color: white; }
        .navbar a { color: white; text-decoration: none; margin-right: 20px; }
        .container { width: 90%; max-width: 1200px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; }
        .menu { display: flex; gap: 15px; margin-top: 20px; flex-wrap: wrap; }
        .menu a { padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .menu a:hover { background: #0056b3; }
        h1 { color: #333; margin-bottom: 20px; }
        .error { color: red; padding: 10px; background: #ffeeee; margin: 10px 0; border-radius: 5px; }
        .success { color: green; padding: 10px; background: #eeffee; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout (<?php echo $_SESSION['fullname']; ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>