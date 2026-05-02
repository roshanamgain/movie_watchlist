<?php
require_once 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ? AND IsActive = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['fullname'] = $user['FullName'];
        $_SESSION['email'] = $user['Email'];
        
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MovieWatchlist</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #14181c;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            max-width: 400px;
            width: 90%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo a {
            font-family: 'Libre Baskerville', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #00e054;
            text-decoration: none;
        }

        .auth-card {
            background: #1c2228;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        .auth-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
            color: #ffffff;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            background: #2c3440;
            border: 1px solid #3a454d;
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #c41e3a;
        }

        .input-group input::placeholder {
            color: #99aabb;
        }

        .checkbox-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .checkbox-group label {
            color: #99aabb;
            cursor: pointer;
        }

        .checkbox-group input {
            margin-right: 8px;
        }

        .forgot-link {
            color: #c41e3a;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: #c41e3a;
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            background: #a01830;
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            color: #99aabb;
            font-size: 0.85rem;
        }

        .auth-footer a {
            color: #00e054;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .error-msg {
            background: rgba(196, 30, 58, 0.2);
            border: 1px solid #c41e3a;
            color: #ff6666;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">
            <a href="index.php">🎭 MovieWatchlist</a>
        </div>
        <div class="auth-card">
            <h1 class="auth-title">Sign In</h1>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgotten?</a>
                </div>
                <button type="submit" class="btn-auth">SIGN IN</button>
            </form>
            <div class="auth-footer">
                Don't have an account? <a href="register.php">Join MovieWatchlist</a>
            </div>
        </div>
    </div>
</body>
</html>