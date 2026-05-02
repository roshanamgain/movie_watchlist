<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password != $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbluser WHERE Email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $created_date = date('Y-m-d');
            
            $stmt = $pdo->prepare("INSERT INTO tbluser (FullName, Email, PasswordHash, CreatedDate, IsActive) VALUES (?, ?, ?, ?, 1)");
            
            if ($stmt->execute([$fullname, $email, $hashed_password, $created_date])) {
                $success = 'Account created successfully! You can now sign in.';
            } else {
                $error = 'Registration failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join MovieWatchlist</title>
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
            max-width: 450px;
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

        .terms-group {
            margin: 20px 0;
        }

        .terms-group label {
            color: #99aabb;
            font-size: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }

        .terms-group input {
            margin-top: 2px;
        }

        .terms-group a {
            color: #00e054;
            text-decoration: none;
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
            margin-top: 10px;
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

        .success-msg {
            background: rgba(0, 224, 84, 0.2);
            border: 1px solid #00e054;
            color: #00e054;
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
            <h1 class="auth-title">Join MovieWatchlist</h1>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Full name" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm password" required>
                </div>
                
                <div class="terms-group">
                    <label>
                        <input type="checkbox" required> I'm at least 16 years old and accept the Terms of Use.
                    </label>
                </div>
                <div class="terms-group">
                    <label>
                        <input type="checkbox" required> I accept the Privacy Policy.
                    </label>
                </div>
                
                <button type="submit" class="btn-auth">SIGN UP</button>
            </form>
            <div class="auth-footer">
                Already have an account? <a href="login.php">Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>